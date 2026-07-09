<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sim;
use App\Models\SimRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
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
        $pendingRequests = SimRequest::with('user', 'sim')->where('status', 'pending')->latest()->get();
        $resolvedRequests = SimRequest::with('user', 'sim')->where('status', '!=', 'pending')->latest()->paginate(10, ['*'], 'requests_page')->appends(request()->query());

        // Fetch assignable users (excluding super_admin role)
        $assignableUsers = User::where('role', '!=', 'super_admin')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $simService = \App\Models\Service::where('name', 'simcard')->first();
        $categories = $simService ? $simService->fields()->active()->pluck('field_name')->toArray() : ['POS SIM', 'CAMERA SIM', 'CCTV', 'ROUTER SIM', 'GPS SIM'];
        $providers = ['mtn', 'airtel', 'glo', '9mobile'];

        // Header statistics
        $totalUploaded = Sim::count();
        $totalAssigned = Sim::where('status', 'assigned')->count();
        $totalAvailable = Sim::where('status', 'available')->count();
        $totalActivated = Sim::where('status', 'active')->count();

        return view('admin.sim-plan.index', compact(
            'sims', 'pendingRequests', 'resolvedRequests', 'assignableUsers', 'categories', 'providers',
            'totalUploaded', 'totalAssigned', 'totalAvailable', 'totalActivated'
        ));
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
                'status'     => 'available',
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

        $sims = Sim::whereIn('id', $request->sim_ids)->get();
        $assignedCount = 0;

        DB::transaction(function () use ($sims, $targetUser, &$assignedCount) {
            foreach ($sims as $sim) {
                if ($sim->status === 'available') {
                    $sim->update([
                        'user_id'    => $targetUser->id,
                        'partner_id' => $targetUser->role === 'partner' ? $targetUser->id : null,
                        'status'     => 'assigned',
                    ]);
                    $assignedCount++;
                }
            }
        });

        return back()->with('success', "Successfully assigned {$assignedCount} SIM number(s) to {$targetUser->first_name} {$targetUser->last_name} ({$targetUser->role}).");
    }

    /**
     * Admin unassigns a SIM back to available pool.
     */
    public function unassign(Request $request, Sim $sim)
    {
        $sim->update([
            'user_id'    => null,
            'partner_id' => null,
            'status'     => 'available',
        ]);

        return back()->with('success', "SIM number {$sim->number} has been unassigned and is now available.");
    }

    /**
     * Admin approves a purchase/activation request.
     */
    public function approveRequest(Request $request, SimRequest $simRequest)
    {
        if ($simRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        try {
            DB::transaction(function () use ($simRequest) {
                // Lock the request for update
                $lockedRequest = SimRequest::where('id', $simRequest->id)->lockForUpdate()->first();
                if (!$lockedRequest || $lockedRequest->status !== 'pending') {
                    throw new \Exception('This request has already been processed or is not found.');
                }

                $sim = $lockedRequest->sim;
                if ($sim) {
                    $sim = Sim::where('id', $sim->id)->lockForUpdate()->first();
                }

                if ($lockedRequest->request_type === 'purchase') {
                    if (!$sim || $sim->status !== 'available') {
                        throw new \Exception('The requested SIM number is no longer available.');
                    }

                    $requester = User::where('id', $lockedRequest->user_id)->lockForUpdate()->first();
                    if (!$requester) {
                        throw new \Exception('Requester user not found.');
                    }

                    $sim->update([
                        'user_id'    => $requester->id,
                        'partner_id' => $requester->role === 'partner' ? $requester->id : null,
                        'status'     => 'assigned',
                    ]);
                } elseif ($lockedRequest->request_type === 'activation') {
                    if ($sim) {
                        $sim->update(['status' => 'active']);

                        // Award commission if configured
                        $service = \App\Models\Service::where('name', 'simcard')->first();
                        $field = null;
                        if ($service) {
                            $field = \App\Models\ServiceField::where('service_id', $service->id)
                                ->where('field_name', $sim->category)
                                ->first();
                        }

                        if ($field) {
                            $awardCommission = function($userId, $role, $simNumber, $simCategory, $simProvider, $roleName) use ($service, $field) {
                                $userModel = \App\Models\User::where('id', $userId)->lockForUpdate()->first();
                                if (!$userModel || $userModel->role === 'super_admin') {
                                    return;
                                }

                                $servicePrice = \App\Models\ServicePrice::where('service_fields_id', $field->id)
                                    ->where('user_type', $userModel->role ?? 'personal')
                                    ->whereNull('user_id')
                                    ->first();

                                $commission = $servicePrice ? (float) $servicePrice->commission : 0.00;

                                if ($commission > 0.00) {
                                    $wallet = \App\Models\Wallet::where('user_id', $userId)->lockForUpdate()->first();
                                    if ($wallet) {
                                        $oldBalance = $wallet->bonus;
                                        $wallet->increment('bonus', $commission);
                                        $newBalance = $wallet->bonus;

                                        $commRef = 'COMM-' . time() . '-' . rand(1000, 9999);

                                        \App\Models\Transaction::create([
                                            'transaction_ref' => $commRef,
                                            'user_id'         => $userId,
                                            'amount'          => $commission,
                                            'fee'             => 0.00,
                                            'net_amount'      => $commission,
                                            'description'     => "Commission earned on SIM Activation (" . $simNumber . ") as {$roleName}",
                                            'type'            => 'credit',
                                            'status'          => 'completed',
                                            'metadata'        => json_encode([
                                                'sim_number' => $simNumber,
                                                'category'   => $simCategory,
                                                'type'       => 'activation_commission',
                                                'role'       => $roleName
                                            ]),
                                            'performed_by' => 'System',
                                        ]);

                                        \App\Models\Report::create([
                                            'user_id'      => $userId,
                                            'phone_number' => $simNumber,
                                            'network'      => $simProvider,
                                            'ref'          => $commRef,
                                            'amount'       => $commission,
                                            'status'       => 'completed',
                                            'type'         => 'commission',
                                            'description'  => "Commission earned on SIM Activation: " . $simNumber . " ({$roleName})",
                                            'old_balance'  => $oldBalance,
                                            'new_balance'  => $newBalance,
                                            'service_id'   => $service->id,
                                        ]);
                                    }
                                }
                            };

                            // 1. Award to the assignee/user (excluding super_admin)
                            if ($sim->user_id) {
                                $awardCommission($sim->user_id, null, $sim->number, $sim->category, $sim->provider, 'assignee');
                            }

                            // 2. Award to the partner (excluding super_admin) if partner is different from assignee
                            if ($sim->partner_id && $sim->partner_id !== $sim->user_id) {
                                $awardCommission($sim->partner_id, null, $sim->number, $sim->category, $sim->provider, 'partner');
                            }
                        }
                    }
                }

                $lockedRequest->update([
                    'status' => 'approved',
                    'admin_notes' => 'Approved by Super Admin.',
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
                    'admin_notes' => $request->admin_notes ?? 'Rejected by Admin.',
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
                        'status'     => 'available',
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
