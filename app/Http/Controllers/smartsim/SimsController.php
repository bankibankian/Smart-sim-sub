<?php

namespace App\Http\Controllers\smartsim;

use App\Http\Controllers\Controller;
use App\Models\Sim;
use App\Models\SimRequest;
use App\Models\User;
use App\Services\SimAssignmentService;
use App\Support\SimAccess;
use App\Support\SimStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimsController extends Controller
{
    /**
     * Device pages exposed via the sidebar, keyed by URL slug.
     * 'category' must match the exact service_fields.field_name / sims.category value in the database.
     */
    private const DEVICE_PAGES = [
        'pos' => [
            'label' => 'POS SIM',
            'category' => 'POS SIM',
            'desc' => 'For payment terminals',
            'icon' => 'credit-card',
            'illustration' => 'pages.welcome2.illustrations.pos-sim',
        ],
        'cctv' => [
            'label' => 'CCTV SIM',
            'category' => 'CCTV',
            'desc' => 'For surveillance systems',
            'icon' => 'video',
            'illustration' => 'pages.welcome2.illustrations.cctv-sim',
        ],
        'router' => [
            'label' => 'Router SIM',
            'category' => 'ROUTER SIM',
            'desc' => 'For mobile routers',
            'icon' => 'router',
            'illustration' => 'pages.welcome2.illustrations.router-sim',
        ],
    ];

    /**
     * Fetch SIM categories and pricing dynamically from the database.
     */
    private function resolveCategories($user): array
    {
        $simService = \App\Models\Service::where('name', 'simcard')->first();
        $categories = [];
        if ($simService) {
            foreach ($simService->fields()->active()->get() as $field) {
                $categories[] = [
                    'name'  => $field->field_name,
                    'price' => $field->priceForUser($user),
                ];
            }
        } else {
            foreach (['POS SIM' => 1000.00, 'CAMERA SIM' => 1500.00, 'CCTV' => 2000.00, 'ROUTER SIM' => 2500.00, 'GPS SIM' => 3000.00] as $name => $price) {
                $categories[] = [
                    'name'  => $name,
                    'price' => $price,
                ];
            }
        }

        return $categories;
    }

    /**
     * SIM Services catalog: illustrated overview of every device SIM, linking to its device page.
     */
    public function overview(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }

        $categories = $this->resolveCategories($user);
        $categoryPrices = collect($categories)->pluck('price', 'name');

        // Roles outside the SIM catalog (Personal, Business, ...) go straight to
        // the request page — they don't browse/self-activate, someone in the
        // hierarchy (or admin) activates on their behalf.
        $goToRequest = !SimAccess::canBrowseCatalog($user);
        $canEarn = SimAccess::canBrowseCatalog($user);

        $devices = [];
        foreach (self::DEVICE_PAGES as $slug => $meta) {
            $devices[] = [
                'slug' => $slug,
                'label' => $meta['label'],
                'desc' => $meta['desc'],
                'illustration' => $meta['illustration'],
                'price' => $categoryPrices[$meta['category']] ?? null,
                'commission' => $canEarn ? $this->resolveCommissionForRole($user, $meta['category']) : null,
                'route' => $goToRequest ? route('sims.' . $slug . '.request') : route('sims.' . $slug),
                'comingSoon' => false,
            ];
        }

        // GPS Tracking SIM is not yet sold — shown as a disabled "coming soon" entry.
        $devices[] = [
            'slug' => 'gps',
            'label' => 'GPS Tracking SIM',
            'desc' => 'For tracking devices',
            'illustration' => 'pages.welcome2.illustrations.gps-sim',
            'price' => null,
            'commission' => null,
            'route' => null,
            'comingSoon' => true,
        ];

        return view('smartsimcard.overview', compact('user', 'devices'));
    }

    /**
     * The per-sale commission this user's role earns for a given SIM category,
     * read from ServicePrice — the same table admin manages per category/role
     * at /admin/services. Mirrors App\Listeners\AwardCommissions.
     */
    private function resolveCommissionForRole(User $user, string $category): ?float
    {
        $service = \App\Models\Service::where('name', 'simcard')->first();
        if (!$service) {
            return null;
        }

        $field = \App\Models\ServiceField::where('service_id', $service->id)->where('field_name', $category)->first();
        if (!$field) {
            return null;
        }

        $servicePrice = \App\Models\ServicePrice::where('service_fields_id', $field->id)
            ->where('user_type', $user->role)
            ->whereNull('user_id')
            ->first();

        return $servicePrice ? (float) $servicePrice->commission : null;
    }

    /**
     * Shared renderer for a single device's request/activation page.
     */
    private function renderDevicePage(string $slug)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }

        $meta = self::DEVICE_PAGES[$slug];
        $category = $meta['category'];
        $categories = $this->resolveCategories($user);
        $price = collect($categories)->firstWhere('name', $category)['price'] ?? null;
        $providers = ['mtn', 'airtel', 'glo', '9mobile'];

        $sims = Sim::where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->role === 'partner') {
                    $q->orWhere('partner_id', $user->id);
                }
            })
            ->where('category', $category)
            ->where('status', '!=', SimStatus::ACTIVATED)
            ->latest()
            ->get();

        return view('smartsimcard.device', [
            'user' => $user,
            'device' => $meta,
            'slug' => $slug,
            'category' => $category,
            'price' => $price,
            'providers' => $providers,
            'sims' => $sims,
        ]);
    }

    public function pos()
    {
        $this->authorizeCatalogAccess();

        return $this->renderDevicePage('pos');
    }

    public function cctv()
    {
        $this->authorizeCatalogAccess();

        return $this->renderDevicePage('cctv');
    }

    public function router()
    {
        $this->authorizeCatalogAccess();

        return $this->renderDevicePage('router');
    }

    /**
     * POS SIM / CCTV SIM / Inventory are only for the catalog roles
     * (Regional Manager, Coordinator, Partner, Agent) — everyone else
     * requests a SIM directly and has it activated for them.
     */
    private function authorizeCatalogAccess(): void
    {
        $user = Auth::user();
        if (!$user || !SimAccess::canBrowseCatalog($user)) {
            abort(403, 'This page is only available to Regional Managers, Coordinators, Partners, and Agents.');
        }
    }

    /**
     * Shared renderer for a device's dedicated "Request a SIM" page.
     */
    private function renderRequestPage(string $slug)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }

        $meta = self::DEVICE_PAGES[$slug];
        $providers = ['mtn', 'airtel', 'glo', '9mobile'];

        return view('smartsimcard.request', [
            'user' => $user,
            'device' => $meta,
            'slug' => $slug,
            'category' => $meta['category'],
            'providers' => $providers,
        ]);
    }

    public function posRequestForm()
    {
        return $this->renderRequestPage('pos');
    }

    public function cctvRequestForm()
    {
        return $this->renderRequestPage('cctv');
    }

    public function routerRequestForm()
    {
        return $this->renderRequestPage('router');
    }

    /**
     * Browsable stock of currently available (unassigned) SIM numbers, by category and network.
     */
    public function inventory(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }
        $this->authorizeCatalogAccess();

        $categories = $this->resolveCategories($user);
        $providers = ['mtn', 'airtel', 'glo', '9mobile'];

        $query = Sim::where('status', SimStatus::UNASSIGNED);

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }
        if ($request->filled('provider')) {
            $query->where('provider', $request->string('provider'));
        }

        $available = $query->orderBy('category')->orderBy('provider')
            ->paginate(15)->withQueryString();

        // Quick stock counts per category, for the summary cards.
        $stockCounts = Sim::where('status', SimStatus::UNASSIGNED)
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('smartsimcard.inventory', compact('user', 'categories', 'providers', 'available', 'stockCounts'));
    }

    /**
     * "My SIM" — the user's own registered SIM cards and submitted requests.
     */
    public function mine(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }
        if (!SimAccess::canViewMine($user)) {
            abort(403, 'My SIM is only available to Personal and Business accounts.');
        }

        $sims = Sim::where('user_id', $user->id)->latest()->paginate(10, ['*'], 'sims_page');

        $requests = SimRequest::with('sim')
            ->where('user_id', $user->id)
            ->latest()->paginate(10, ['*'], 'requests_page');

        return view('smartsimcard.mine', compact('user', 'sims', 'requests'));
    }

    /**
     * Get available numbers based on category and provider (AJAX endpoint).
     */
    public function getAvailableNumbers(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'provider' => 'required|string',
        ]);

        $numbers = Sim::where('category', $request->category)
            ->where('provider', $request->provider)
            ->where('status', SimStatus::UNASSIGNED)
            ->orderBy('number')
            ->get(['id', 'number']);

        return response()->json($numbers);
    }

    /**
     * User submits request to purchase/get a SIM number.
     */
    public function requestSim(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'provider' => 'required|string',
            'sim_id'   => 'required|exists:sims,id',
        ]);

        $user = Auth::user();
        $sim = Sim::find($request->sim_id);

        if ($sim->status !== SimStatus::UNASSIGNED) {
            return back()->with('error', 'The selected SIM number is no longer available.');
        }

        // Check if there is already a pending request for this SIM to prevent double-charging
        $existingRequest = SimRequest::where('sim_id', $sim->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'This SIM card already has a pending request.');
        }

        try {
            DB::transaction(function () use ($user, $sim) {
                // Create the SIM request with 0.00 amount (free request)
                SimRequest::create([
                    'user_id'      => $user->id,
                    'sim_id'       => $sim->id,
                    'number'       => $sim->number,
                    'category'     => $sim->category,
                    'provider'     => $sim->provider,
                    'request_type' => 'purchase',
                    'status'       => 'pending',
                    'amount'       => 0.00,
                ]);
            });

            return back()->with('success', 'Your request for SIM number ' . $sim->number . ' has been submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * User submits request to activate their assigned SIM.
     */
    public function activateSim(Request $request)
    {
        $request->validate([
            'sim_id' => 'required|exists:sims,id',
        ]);

        $user = Auth::user();

        if (!SimAccess::canBrowseCatalog($user)) {
            return back()->with('error', 'Self-activation isn\'t available on your account — your partner or admin will activate this SIM for you.');
        }

        $sim = Sim::find($request->sim_id);

        // Ensure the SIM belongs to the user or partner
        if ($sim->user_id !== $user->id && $sim->partner_id !== $user->id) {
            return back()->with('error', 'Access denied. You do not own this SIM card.');
        }

        if ($sim->status === SimStatus::ACTIVATED) {
            return back()->with('error', 'This SIM card is already active.');
        }

        // Check if there is already a pending activation request
        $existing = SimRequest::where('sim_id', $sim->id)
            ->where('request_type', 'activation')
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'There is already a pending activation request for this number.');
        }

        // 1. Resolve pricing
        $simService = \App\Models\Service::where('name', 'simcard')->first();
        $serviceField = null;
        $payableAmount = 0.00;
        if ($simService) {
            $serviceField = \App\Models\ServiceField::where('service_id', $simService->id)
                ->where('field_name', $sim->category)
                ->first();
        }
        if ($serviceField) {
            $payableAmount = $serviceField->priceForUser($user);
        }

        // 2. Database Transaction to charge wallet and create request
        try {
            DB::transaction(function () use ($user, $sim, $payableAmount, $simService) {
                // Lock wallet
                $wallet = \App\Models\Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$wallet || $wallet->balance < $payableAmount) {
                    throw new \Exception('Insufficient wallet balance! You need ₦' . number_format($payableAmount, 2));
                }

                $oldBalance = $wallet->balance;
                $wallet->decrement('balance', $payableAmount);
                $newBalance = $wallet->balance;

                $ref = 'ACT-' . time() . '-' . rand(1000, 9999);

                // Create Transaction record
                \App\Models\Transaction::create([
                    'transaction_ref' => $ref,
                    'user_id'         => $user->id,
                    'amount'          => $payableAmount,
                    'fee'             => 0.00,
                    'net_amount'      => $payableAmount,
                    'description'     => "SIM Card Activation: Request for number {$sim->number} (Category: {$sim->category}, Network: " . strtoupper($sim->provider) . ")",
                    'type'            => 'debit',
                    'status'          => 'completed',
                    'performed_by'    => $user->first_name . ' ' . $user->last_name,
                    'approved_by'     => $user->id,
                ]);

                // Create Report record
                \App\Models\Report::create([
                    'user_id'      => $user->id,
                    'phone_number' => $sim->number,
                    'network'      => $sim->provider,
                    'ref'          => $ref,
                    'amount'       => $payableAmount,
                    'status'       => 'completed',
                    'type'         => 'sim_activation',
                    'description'  => "SIM Card Activation: Request for number {$sim->number} (Category: {$sim->category})",
                    'old_balance'  => $oldBalance,
                    'new_balance'  => $newBalance,
                    'service_id'   => $simService ? $simService->id : null,
                ]);

                // Create the SIM request
                SimRequest::create([
                    'user_id'      => $user->id,
                    'sim_id'       => $sim->id,
                    'number'       => $sim->number,
                    'category'     => $sim->category,
                    'provider'     => $sim->provider,
                    'request_type' => 'activation',
                    'status'       => 'pending',
                    'amount'       => $payableAmount,
                ]);
            });

            $chargeMsg = $payableAmount > 0 ? " ₦" . number_format($payableAmount, 2) . " has been charged from your wallet." : "";
            return back()->with('success', 'Activation request for ' . $sim->number . ' has been submitted successfully.' . $chargeMsg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * A Regional Manager / Coordinator / Partner delegates a SIM they currently
     * hold down to their direct subordinate. Also preserves the legacy
     * Partner -> agent/business assignment, which sits outside the new
     * Regional Manager -> Coordinator -> Partner -> User hierarchy.
     */
    public function partnerAssignSim(Request $request)
    {
        $request->validate([
            'sim_id'  => 'required|exists:sims,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $from = Auth::user();
        $sim = Sim::find($request->sim_id);
        $targetUser = User::find($request->user_id);

        // Legacy path: partner delegating to an agent/business account (outside the hierarchy).
        if ($from->role === 'partner' && in_array($targetUser->role, ['agent', 'business'])) {
            if ($sim->partner_id !== $from->id || $sim->user_id !== $from->id) {
                return back()->with('error', 'You can only assign numbers currently allocated to you.');
            }

            $sim->update([
                'user_id' => $targetUser->id,
                'status'  => SimStatus::ASSIGNED_TO_PARTNER,
            ]);

            return back()->with('success', "SIM number {$sim->number} successfully assigned to {$targetUser->first_name} {$targetUser->last_name}.");
        }

        // Hierarchy path: Regional Manager -> Coordinator, Coordinator -> Partner, Partner -> User.
        try {
            app(SimAssignmentService::class)->assignDown($sim, $from, $targetUser);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "SIM number {$sim->number} successfully assigned to {$targetUser->first_name} {$targetUser->last_name}.");
    }

    /**
     * Public/User SIM lookup check.
     */
    public function checkNumber(Request $request)
    {
        $request->validate([
            'number' => 'required|string',
        ]);

        $sim = Sim::with('user')->where('number', $request->number)->first();

        if (!$sim) {
            return back()->with('check_result', [
                'success' => false,
                'message' => 'SIM number not found in the database.',
            ]);
        }

        if ($sim->user_id && $sim->user) {
            $isOwnerOrAdmin = ($sim->user_id === Auth::id()) || (Auth::user() && Auth::user()->role === 'super_admin');

            return back()->with('check_result', [
                'success'      => true,
                'found'        => true,
                'assigned'     => true,
                'number'       => $sim->number,
                'category'     => $sim->category,
                'provider'     => $sim->provider,
                'status'       => $sim->status,
                'user_name'    => $isOwnerOrAdmin ? ($sim->user->first_name . ' ' . $sim->user->last_name) : 'Masked (Unauthorized)',
                'user_email'   => $isOwnerOrAdmin ? $sim->user->email : 'Masked (Unauthorized)',
                'user_phone'   => $isOwnerOrAdmin ? $sim->user->phone : 'Masked (Unauthorized)',
            ]);
        }

        return back()->with('check_result', [
            'success'  => true,
            'found'    => true,
            'assigned' => false,
            'number'   => $sim->number,
            'category' => $sim->category,
            'provider' => $sim->provider,
            'status'   => $sim->status,
        ]);
    }
}
