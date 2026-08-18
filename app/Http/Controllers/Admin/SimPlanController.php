<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GrantActivationBonusData;
use App\Models\Report;
use App\Models\ServiceField;
use App\Models\Sim;
use App\Models\SimRequest;
use App\Models\SimSwapRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\SimAssignmentService;
use App\Support\SimStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SimPlanController extends Controller
{
    /**
     * Display the Admin SIM Management page.
     */
    public function index(Request $request)
    {
        $query = Sim::with(['user', 'partner']);

        // Search by SIM Number
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%");
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by Provider
        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sims = $query->latest()->paginate(10, ['*'], 'sims_page')->appends(request()->query());

        // Fetch requests
        $pendingRequests = SimRequest::with('user', 'sim', 'upline')->where('status', 'pending')->latest()->get();
        $resolvedRequests = SimRequest::with('user', 'sim')->where('status', '!=', 'pending')->latest()->paginate(10, ['*'], 'requests_page')->appends(request()->query());

        // Fetch swap requests
        $pendingSwaps = SimSwapRequest::with('sim', 'requester', 'fromHolder', 'toHolder')->where('status', 'pending')->latest()->get();
        $resolvedSwaps = SimSwapRequest::with('sim', 'requester', 'fromHolder', 'toHolder', 'approver')->where('status', '!=', 'pending')->latest()->paginate(10, ['*'], 'swaps_page')->appends(request()->query());

        // Fetch assignable users (excluding super_admin role)
        $assignableUsers = User::where('role', '!=', 'super_admin')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $simService = \App\Models\Service::where('name', 'simcard')->first();
        $categoryFields = $simService ? $simService->fields()->active()->get() : collect();
        $categories = $simService ? $categoryFields->pluck('field_name')->toArray() : ['POS SIM', 'CAMERA SIM', 'CCTV', 'ROUTER SIM', 'GPS SIM'];
        $providers = ['mtn', 'airtel', 'glo', '9mobile'];

        // Failed activation-bonus-data top-ups, written by GrantActivationBonusData
        // to the shared reports table — same source every other purchase-attempt log uses.
        $failedActivations = Report::where('type', 'activation_bonus_data')
            ->where('status', 'failed')
            ->latest()
            ->paginate(10, ['*'], 'failed_page')
            ->appends(request()->query());

        // Header statistics
        $totalUploaded = Sim::count();
        $totalAssigned = Sim::whereIn('status', [
            SimStatus::ASSIGNED_TO_RM,
            SimStatus::ASSIGNED_TO_COORDINATOR,
            SimStatus::ASSIGNED_TO_PARTNER,
        ])->count();
        $totalAvailable = Sim::where('status', SimStatus::UNASSIGNED)->count();
        $totalActivated = Sim::where('status', SimStatus::ACTIVATED)->count();

        return view('admin.sim-plan.index', compact(
            'sims', 'pendingRequests', 'resolvedRequests', 'pendingSwaps', 'resolvedSwaps', 'assignableUsers', 'categories', 'categoryFields', 'providers',
            'totalUploaded', 'totalAssigned', 'totalAvailable', 'totalActivated', 'failedActivations'
        ));
    }

    /**
     * Flip the per-category activation kill-switch. Blocks new activations
     * for every SIM in this category via SimAssignmentService::activate()'s
     * server-side gate — existing activated SIMs are unaffected. Redirects
     * back to the Activation Controls section specifically (not just the
     * top of the page) so the admin lands right back on the switch they
     * just flipped and sees the new state immediately, instead of losing
     * their scroll position on reload.
     */
    public function toggleActivation(ServiceField $field): RedirectResponse
    {
        $field->update(['activation_disabled' => !$field->activation_disabled]);
        $state = $field->activation_disabled ? 'disabled' : 're-enabled';

        return redirect(url()->previous() . '#activation-controls')
            ->with('success', "Activation {$state} for {$field->field_name}.");
    }

    /**
     * Re-dispatch the activation bonus data top-up for a failed attempt,
     * looked up by phone number (the SIM's own number) since GrantActivationBonusData
     * takes a Sim, not a Report.
     */
    public function retryActivationBonus(Report $report): RedirectResponse
    {
        if ($report->type !== 'activation_bonus_data' || $report->status !== 'failed') {
            return back()->with('error', 'This entry is not a retryable failed activation bonus.');
        }

        $sim = Sim::where('number', $report->phone_number)->first();
        if (!$sim) {
            return back()->with('error', "No SIM found for {$report->phone_number}.");
        }

        GrantActivationBonusData::dispatch($sim);

        return back()->with('success', "Retry queued for {$report->phone_number}.");
    }

    /**
     * Admin uploads new available SIM numbers to the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'provider' => 'required|string',
            'numbers'  => 'required|string',
        ]);

        $rawNumbers = preg_split('/[\r\n,]+/', $request->numbers);
        $addedCount = 0;
        $failedNumbers = [];

        foreach ($rawNumbers as $rawNumber) {
            $number = trim($rawNumber);
            if (empty($number)) {
                continue;
            }

            if (!preg_match('/^[0-9]+$/', $number)) {
                $failedNumbers[] = $number . ' (Invalid format)';
                continue;
            }

            if (Sim::where('number', $number)->exists()) {
                $failedNumbers[] = $number . ' (Duplicate)';
                continue;
            }

            Sim::create([
                'number'     => $number,
                'category'   => $request->category,
                'provider'   => $request->provider,
                'status'     => SimStatus::UNASSIGNED,
                'user_id'    => null,
                'partner_id' => null,
            ]);

            $addedCount++;
        }

        $message = "Successfully added {$addedCount} SIM number(s).";
        if (count($failedNumbers) > 0) {
            $message .= " Failed: " . implode(', ', $failedNumbers);
            return back()->with('warning', $message);
        }

        return back()->with('success', $message);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'sim_ids'   => 'required|array|max:50',
            'sim_ids.*' => 'exists:sims,id',
            'user_id'   => 'required|exists:users,id',
        ]);

        $targetUser = User::find($request->user_id);
        $actor = $request->user();
        $service = app(SimAssignmentService::class);

        $sims = Sim::whereIn('id', $request->sim_ids)->get();
        $assignedCount = 0;

        DB::transaction(function () use ($sims, $targetUser, $actor, $service, &$assignedCount) {
            foreach ($sims as $sim) {
                if ($sim->status === SimStatus::UNASSIGNED) {
                    $service->adminAssign($sim, $targetUser, $actor);
                    $assignedCount++;
                }
            }
        });

        return back()->with('success', "Successfully assigned {$assignedCount} SIM number(s) to {$targetUser->first_name} {$targetUser->last_name} ({$targetUser->role}).");
    }

    /**
     * AJAX: unassigned SIMs for a category/provider, used by the purchase-request
     * approval picker so the admin can choose exactly which numbers to hand out.
     */
    public function availableSims(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'provider' => 'required|string',
        ]);

        $sims = Sim::where('category', $request->category)
            ->where('provider', $request->provider)
            ->where('status', SimStatus::UNASSIGNED)
            ->orderBy('number')
            ->get(['id', 'number']);

        return response()->json($sims);
    }

    /**
     * Admin unassigns a SIM back to the available pool, at any stage of the cascade.
     */
    public function unassign(Request $request, Sim $sim)
    {
        app(SimAssignmentService::class)->unassign($sim, $request->user());

        return back()->with('success', "SIM number {$sim->number} has been unassigned and is now available.");
    }

    /**
     * Admin activates a SIM directly, without going through a user's activation request.
     */
    public function activate(Request $request, Sim $sim)
    {
        try {
            app(SimAssignmentService::class)->activate($sim, $request->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "SIM number {$sim->number} has been activated.");
    }

    /**
     * Admin approves a purchase/activation request. For a purchase request
     * the admin explicitly picks which SIM(s) to hand out — the requester
     * only stated category/provider/quantity, not specific numbers.
     */
    public function approveRequest(Request $request, SimRequest $simRequest)
    {
        if ($simRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $actor = $request->user();

        if ($simRequest->request_type === 'purchase') {
            $request->validate([
                'sim_ids'   => 'required|array',
                'sim_ids.*' => 'exists:sims,id',
            ]);

            if (count($request->sim_ids) !== $simRequest->quantity) {
                return back()->with('error', "Please select exactly {$simRequest->quantity} SIM number(s).");
            }
        }

        try {
            DB::transaction(function () use ($simRequest, $actor, $request) {
                // Lock the request for update
                $lockedRequest = SimRequest::where('id', $simRequest->id)->lockForUpdate()->first();
                if (!$lockedRequest || $lockedRequest->status !== 'pending') {
                    throw new \Exception('This request has already been processed or is not found.');
                }

                if ($lockedRequest->request_type === 'purchase') {
                    $requester = User::where('id', $lockedRequest->user_id)->lockForUpdate()->first();
                    if (!$requester) {
                        throw new \Exception('Requester user not found.');
                    }

                    $sims = Sim::whereIn('id', $request->sim_ids)->lockForUpdate()->get();
                    if ($sims->count() !== $lockedRequest->quantity) {
                        throw new \Exception('One or more selected SIMs are no longer available.');
                    }

                    foreach ($sims as $sim) {
                        if ($sim->status !== SimStatus::UNASSIGNED) {
                            throw new \Exception("SIM {$sim->number} is no longer available.");
                        }
                    }

                    $service = app(SimAssignmentService::class);
                    foreach ($sims as $sim) {
                        $service->adminAssign($sim, $requester, $actor);
                    }
                } elseif ($lockedRequest->request_type === 'activation') {
                    $sim = $lockedRequest->sim;
                    if ($sim) {
                        $sim = Sim::where('id', $sim->id)->lockForUpdate()->first();
                    }

                    if ($sim && $sim->status !== SimStatus::ACTIVATED) {
                        // Marks the SIM ACTIVATED and fires SimActivated, which the
                        // Commission Engine (AwardCommissions listener) pays out from.
                        app(SimAssignmentService::class)->activate($sim, $actor);
                    }
                }

                $lockedRequest->update([
                    'status' => 'approved',
                    'admin_notes' => 'Approved.',
                ]);
            });

            return back()->with('success', 'Request approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin rejects a purchase/activation request.
     */
    public function rejectRequest(Request $request, SimRequest $simRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        if ($simRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        try {
            DB::transaction(function () use ($simRequest, $request) {
                // Lock the request for update
                $lockedRequest = SimRequest::where('id', $simRequest->id)->lockForUpdate()->first();
                if (!$lockedRequest || $lockedRequest->status !== 'pending') {
                    throw new \Exception('This request has already been processed or is not found.');
                }

                $lockedRequest->update([
                    'status'      => 'rejected',
                    'admin_notes' => $request->admin_notes ?? 'Rejected.',
                ]);

                // Refund the amount securely if user was charged
                if ($lockedRequest->amount > 0) {
                    $wallet = Wallet::where('user_id', $lockedRequest->user_id)->lockForUpdate()->first();
                    if ($wallet) {
                        $oldBalance = $wallet->balance;
                        $wallet->increment('balance', $lockedRequest->amount);
                        $newBalance = $wallet->balance;

                        $refundRef = 'REF-' . time() . '-' . rand(1000, 9999);

                        // Create refund transaction
                        Transaction::create([
                            'transaction_ref' => $refundRef,
                            'user_id'         => $lockedRequest->user_id,
                            'amount'          => $lockedRequest->amount,
                            'fee'             => 0.00,
                            'net_amount'      => $lockedRequest->amount,
                            'description'     => "Refund: Rejected SIM card request ({$lockedRequest->request_type}) for number {$lockedRequest->number}",
                            'type'            => 'refund',
                            'status'          => 'completed',
                            'performed_by'    => 'System Admin',
                            'approved_by'     => auth()->id(),
                        ]);

                        // Create Report record
                        \App\Models\Report::create([
                            'user_id'      => $lockedRequest->user_id,
                            'phone_number' => $lockedRequest->number,
                            'network'      => $lockedRequest->provider,
                            'ref'          => $refundRef,
                            'amount'       => $lockedRequest->amount,
                            'status'       => 'completed',
                            'type'         => 'refund',
                            'description'  => "Refund: Rejected SIM card request ({$lockedRequest->request_type}) for number {$lockedRequest->number}",
                            'old_balance'  => $oldBalance,
                            'new_balance'  => $newBalance,
                        ]);
                    }
                }
            });

            $refundMessage = $simRequest->amount > 0 ? " and refunded ₦" . number_format($simRequest->amount, 2) . " to their wallet" : "";
            return back()->with('success', 'Request rejected successfully' . $refundMessage . '.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error rejecting request: ' . $e->getMessage());
        }
    }

    /**
     * Download a sample Excel template for bulk SIM upload.
     */
    public function downloadSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $sheet->setCellValue('A1', 'number');
        $sheet->setCellValue('B1', 'category');
        $sheet->setCellValue('C1', 'provider');
        
        // Sample Rows
        $sheet->setCellValue('A2', '08031234567');
        $sheet->setCellValue('B2', 'POS SIM');
        $sheet->setCellValue('C2', 'mtn');
        
        $sheet->setCellValue('A3', '09051234567');
        $sheet->setCellValue('B3', 'CCTV');
        $sheet->setCellValue('C3', 'glo');

        $sheet->setCellValue('A4', '08091234567');
        $sheet->setCellValue('B4', 'ROUTER SIM');
        $sheet->setCellValue('C4', '9mobile');
        
        // Auto size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="sim_bulk_upload_sample.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Import SIM numbers from Excel/CSV file.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $file = $request->file('excel_file');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            
            if (count($rows) <= 1) {
                return back()->with('error', 'The uploaded Excel file has no data rows.');
            }

            $headerRow = array_shift($rows);
            
            $numKey = null;
            $catKey = null;
            $provKey = null;

            foreach ($headerRow as $key => $val) {
                $cleaned = strtolower(trim($val));
                if ($cleaned === 'number') {
                    $numKey = $key;
                } elseif ($cleaned === 'category') {
                    $catKey = $key;
                } elseif ($cleaned === 'provider') {
                    $provKey = $key;
                }
            }

            if (!$numKey || !$catKey || !$provKey) {
                return back()->with('error', 'Excel file headers must contain: number, category, provider.');
            }

            $addedCount = 0;
            $failedNumbers = [];

            $simService = \App\Models\Service::where('name', 'simcard')->first();
            $validCategories = $simService ? $simService->fields()->active()->pluck('field_name')->toArray() : ['POS SIM', 'CAMERA SIM', 'CCTV', 'ROUTER SIM', 'GPS SIM'];

            DB::transaction(function () use ($rows, $numKey, $catKey, $provKey, &$addedCount, &$failedNumbers, $validCategories) {
                $validProviders = ['mtn', 'airtel', 'glo', '9mobile'];

                foreach ($rows as $rowNum => $row) {
                    $number = trim($row[$numKey] ?? '');
                    $category = trim($row[$catKey] ?? '');
                    $provider = strtolower(trim($row[$provKey] ?? ''));

                    if (empty($number) && empty($category) && empty($provider)) {
                        continue;
                    }

                    if (empty($number) || !preg_match('/^[0-9]+$/', $number)) {
                        $failedNumbers[] = "Row {$rowNum}: " . ($number ?: 'Empty') . " (Invalid format)";
                        continue;
                    }

                    if (!in_array($category, $validCategories)) {
                        $failedNumbers[] = "Row {$rowNum}: {$number} (Invalid category '{$category}')";
                        continue;
                    }

                    if (!in_array($provider, $validProviders)) {
                        $failedNumbers[] = "Row {$rowNum}: {$number} (Invalid provider '{$provider}')";
                        continue;
                    }

                    if (Sim::where('number', $number)->exists()) {
                        $failedNumbers[] = "Row {$rowNum}: {$number} (Duplicate)";
                        continue;
                    }

                    Sim::create([
                        'number'     => $number,
                        'category'   => $category,
                        'provider'   => $provider,
                        'status'     => SimStatus::UNASSIGNED,
                        'user_id'    => null,
                        'partner_id' => null,
                    ]);

                    $addedCount++;
                }
            });

            $message = "Successfully imported {$addedCount} SIM number(s) from Excel.";
            if (count($failedNumbers) > 0) {
                $message .= " Errors: " . implode(', ', array_slice($failedNumbers, 0, 5));
                if (count($failedNumbers) > 5) {
                    $message .= " and " . (count($failedNumbers) - 5) . " more errors.";
                }
                return back()->with('warning', $message);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Error reading Excel file: ' . $e->getMessage());
        }
    }
}
