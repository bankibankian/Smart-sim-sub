<?php

namespace App\Http\Controllers\smartsim;

use App\Http\Controllers\Controller;
use App\Models\Sim;
use App\Models\SimRequest;
use App\Models\SimSwapRequest;
use App\Models\User;
use App\Services\SimAssignmentService;
use App\Support\RoleHierarchy;
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
     * Shared renderer for a single device's request/activation page. The
     * left-panel action differs by role: Regional Manager / Coordinator only
     * distribute a held SIM down to their next-tier downline; Partner
     * activates a held SIM for one of their downline Users (debiting that
     * user, not themselves); Agent keeps the old self-activate flow.
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

        $mode = match ($user->role) {
            'regional_manager', 'coordinator' => 'distribute',
            'partner' => 'activate_for_user',
            default => 'self_activate',
        };

        $downlineUsers = collect();

        $heldSims = match ($mode) {
            'distribute' => Sim::where($user->role === 'regional_manager' ? 'regional_manager_id' : 'coordinator_id', $user->id)
                ->where('status', $user->role === 'regional_manager' ? SimStatus::ASSIGNED_TO_RM : SimStatus::ASSIGNED_TO_COORDINATOR)
                ->where('category', $category)
                ->latest()->get(),
            'activate_for_user' => Sim::where('partner_id', $user->id)
                ->where('status', SimStatus::ASSIGNED_TO_PARTNER)
                ->where('category', $category)
                ->latest()->get(),
            default => Sim::where('user_id', $user->id)
                ->where('category', $category)
                ->where('status', '!=', SimStatus::ACTIVATED)
                ->latest()->get(),
        };

        $personalPrice = null;

        if ($mode === 'distribute') {
            $nextRole = \App\Support\RoleHierarchy::nextRole($user->role);
            $downlineUsers = $user->referrals()->where('role', $nextRole)->where('status', 'active')->orderBy('first_name')->get();
        } elseif ($mode === 'activate_for_user') {
            $downlineUsers = $user->referrals()->where('role', 'personal')->where('status', 'active')->orderBy('first_name')->get();

            // The fee shown is what the selected downline User (always 'personal') pays, not the partner's own rate.
            $simService = \App\Models\Service::where('name', 'simcard')->first();
            $field = $simService ? \App\Models\ServiceField::where('service_id', $simService->id)->where('field_name', $category)->first() : null;
            $personalPrice = $field
                ? (float) (\App\Models\ServicePrice::where('service_fields_id', $field->id)->where('user_type', 'personal')->whereNull('user_id')->value('price') ?? $field->base_price)
                : null;
        }

        return view('smartsimcard.device', [
            'user' => $user,
            'device' => $meta,
            'personalPrice' => $personalPrice,
            'slug' => $slug,
            'category' => $category,
            'price' => $price,
            'providers' => $providers,
            'mode' => $mode,
            'heldSims' => $heldSims,
            'downlineUsers' => $downlineUsers,
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
     * The user's SIM portfolio, split into three tabs: Activated, sitting
     * with the user right now (Assigned to Me), and further down their
     * downline (Assigned to Others — some of which may be swap-eligible).
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

        $baseQuery = function () use ($request, $user) {
            $query = $this->scopeToUserPortfolio(Sim::query(), $user);
            if ($request->filled('category')) {
                $query->where('category', $request->string('category'));
            }
            if ($request->filled('provider')) {
                $query->where('provider', $request->string('provider'));
            }
            return $query;
        };

        $activatedSims = $baseQuery()
            ->whereIn('status', [SimStatus::ACTIVATED, SimStatus::DEACTIVATED, SimStatus::SUSPENDED])
            ->orderBy('category')->orderBy('provider')
            ->paginate(15, ['*'], 'activated_page')->withQueryString();

        $assignedToMeQuery = $baseQuery();
        $this->applyOwnTierScope($assignedToMeQuery, $user);
        $assignedToMeSims = $assignedToMeQuery
            ->orderBy('category')->orderBy('provider')
            ->paginate(15, ['*'], 'mine_page')->withQueryString();

        $assignedToOthersQuery = $baseQuery();
        $this->applyOthersScope($assignedToOthersQuery, $user);
        $assignedToOthersSims = $assignedToOthersQuery
            ->with(['user', 'partner', 'coordinator'])
            ->orderBy('category')->orderBy('provider')
            ->paginate(15, ['*'], 'others_page')->withQueryString();

        // Flag which "Assigned to Others" rows the user can swap, and resolve
        // who currently holds each one for the "Held By" column — derived
        // purely from the SIM's own state, so it's correct regardless of
        // which tier is viewing (a Coordinator's "Others" tab can show rows
        // still held by a Partner *and* rows that Partner has already
        // handed to their own downline user).
        $assignedToOthersSims->getCollection()->transform(function (Sim $sim) use ($user) {
            $sim->swap_eligible = (bool) $this->resolveSwapHolder($sim, $user);
            $sim->current_holder = match (true) {
                $sim->status === SimStatus::ASSIGNED_TO_COORDINATOR => $sim->coordinator,
                // At ASSIGNED_TO_PARTNER, user_id === partner_id means it's
                // still self-held by the partner; anything else means it's
                // been handed to that downline personal user.
                $sim->status === SimStatus::ASSIGNED_TO_PARTNER && $sim->user_id !== $sim->partner_id => $sim->user,
                $sim->status === SimStatus::ASSIGNED_TO_PARTNER => $sim->partner,
                default => null,
            };

            return $sim;
        });

        // Quick counts per category, for the summary cards — scoped to this user's own SIMs.
        $stockCounts = $this->scopeToUserPortfolio(Sim::query(), $user)
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        // Who this user can bulk-assign/swap held SIMs down to — empty for
        // agents, who sit at the bottom of the chain and only self-activate.
        $nextRole = RoleHierarchy::nextRole($user->role);
        $downlineUsers = $nextRole
            ? $user->referrals()->where('role', $nextRole)->where('status', 'active')->orderBy('first_name')->get()
            : collect();

        return view('smartsimcard.inventory', compact(
            'user', 'categories', 'providers', 'stockCounts', 'downlineUsers',
            'activatedSims', 'assignedToMeSims', 'assignedToOthersSims'
        ));
    }

    /**
     * Constrain $query to SIMs currently held AT $user's own tier (not yet
     * cascaded further, not activated).
     */
    private function applyOwnTierScope($query, User $user): void
    {
        match ($user->role) {
            'regional_manager' => $query->where('regional_manager_id', $user->id)->where('status', SimStatus::ASSIGNED_TO_RM),
            'coordinator' => $query->where('coordinator_id', $user->id)->where('status', SimStatus::ASSIGNED_TO_COORDINATOR),
            // A partner's own held SIMs share status+partner_id with SIMs
            // they've already handed to a downline user — both adminAssign()
            // and assignDown() set user_id to the partner's OWN id when it's
            // still with them (never left null), overwriting it with the
            // downline user's id only once handed further down. So
            // user_id = partner_id is what "still with the partner" means.
            'partner' => $query->where('partner_id', $user->id)->where('status', SimStatus::ASSIGNED_TO_PARTNER)->whereColumn('user_id', 'partner_id'),
            default => $query->where('user_id', $user->id)->where('status', SimStatus::ASSIGNED_TO_PARTNER), // agent
        };
    }

    /**
     * Constrain $query to SIMs cascaded further down $user's downline than
     * their own tier, not yet activated.
     */
    private function applyOthersScope($query, User $user): void
    {
        match ($user->role) {
            'regional_manager' => $query->where('regional_manager_id', $user->id)
                ->whereIn('status', [SimStatus::ASSIGNED_TO_COORDINATOR, SimStatus::ASSIGNED_TO_PARTNER]),
            'coordinator' => $query->where('coordinator_id', $user->id)->where('status', SimStatus::ASSIGNED_TO_PARTNER),
            'partner' => $query->where('partner_id', $user->id)->where('status', SimStatus::ASSIGNED_TO_PARTNER)
                ->whereColumn('user_id', '!=', 'partner_id'),
            default => $query->whereRaw('1 = 0'), // agent has no downline
        };
    }

    /**
     * Whether $sim is swap-eligible for $user at their direct next tier,
     * and if so, who currently holds it and at what tier. Returns null if
     * not eligible. Single source of truth, used both to flag rows on the
     * Inventory page and to re-validate requestSwap() submissions.
     */
    private function resolveSwapHolder(Sim $sim, User $user): ?array
    {
        return match (true) {
            $user->role === 'regional_manager'
                && $sim->regional_manager_id === $user->id
                && $sim->status === SimStatus::ASSIGNED_TO_COORDINATOR
                => ['holder_id' => $sim->coordinator_id, 'holder_role' => 'coordinator'],

            $user->role === 'coordinator'
                && $sim->coordinator_id === $user->id
                && $sim->status === SimStatus::ASSIGNED_TO_PARTNER
                && $sim->user_id === $sim->partner_id
                => ['holder_id' => $sim->partner_id, 'holder_role' => 'partner'],

            $user->role === 'partner'
                && $sim->partner_id === $user->id
                && $sim->status === SimStatus::ASSIGNED_TO_PARTNER
                && $sim->user_id !== $sim->partner_id
                => ['holder_id' => $sim->user_id, 'holder_role' => 'personal'],

            default => null,
        };
    }

    /**
     * User requests that a SIM currently held by one of their direct
     * downline be moved to a different downline peer at that same tier.
     * The actual move only happens once an admin approves it.
     */
    public function requestSwap(Request $request)
    {
        $request->validate([
            'sim_id' => 'required|exists:sims,id',
            'to_holder_id' => 'required|exists:users,id',
        ]);

        $requester = Auth::user();
        $sim = Sim::find($request->sim_id);

        $context = $this->resolveSwapHolder($sim, $requester);
        if (!$context) {
            return back()->with('error', 'This SIM is not eligible for a swap.');
        }

        $toHolder = User::find($request->to_holder_id);

        if ((int) $toHolder->id === (int) $context['holder_id']) {
            return back()->with('error', 'Please pick a different downline account to swap to.');
        }

        if ($toHolder->role !== $context['holder_role'] || $toHolder->referred_by !== $requester->id || $toHolder->status !== 'active') {
            return back()->with('error', 'You can only swap to another active account in your own downline at that tier.');
        }

        if (SimSwapRequest::where('sim_id', $sim->id)->where('status', 'pending')->exists()) {
            return back()->with('error', 'A swap request for this SIM is already pending admin approval.');
        }

        SimSwapRequest::create([
            'sim_id' => $sim->id,
            'requested_by' => $requester->id,
            'from_holder_id' => $context['holder_id'],
            'to_holder_id' => $toHolder->id,
            'holder_role' => $context['holder_role'],
            'status' => 'pending',
        ]);

        return back()->with('success', "Swap request for {$sim->number} submitted for admin approval.");
    }

    /**
     * Scope a SIM query to only the rows tied to this user via their role's
     * FK column — their full portfolio, at any status, since earlier tiers'
     * FK columns are never cleared as a SIM cascades further down the chain.
     */
    private function scopeToUserPortfolio($query, User $user)
    {
        return match ($user->role) {
            'regional_manager' => $query->where('regional_manager_id', $user->id),
            'coordinator' => $query->where('coordinator_id', $user->id),
            'partner' => $query->where('partner_id', $user->id),
            default => $query->where('user_id', $user->id), // agent
        };
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
     * User submits a request for N SIM numbers of a category/network — they
     * no longer pick specific numbers; the admin chooses exactly which SIMs
     * to hand out when reviewing the request.
     */
    public function requestSim(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'provider' => 'required|string',
            'quantity' => 'required|integer|min:1|max:20',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user, $request) {
                SimRequest::create([
                    'user_id'      => $user->id,
                    'sim_id'       => null,
                    'number'       => null,
                    'category'     => $request->category,
                    'provider'     => $request->provider,
                    'quantity'     => $request->quantity,
                    'request_type' => 'purchase',
                    'status'       => 'pending',
                    'amount'       => 0.00,
                ]);
            });

            return back()->with('success', "Your request for {$request->quantity} x {$request->category} has been submitted successfully.");
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

        if ($user->role !== 'agent') {
            return back()->with('error', 'Self-activation isn\'t available on your account. Regional Managers and Coordinators distribute SIMs down the chain; Partners activate for their downline Users.');
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
     * Bulk variant of the hierarchy path above — lets a Regional Manager /
     * Coordinator / Partner multi-select held SIMs on the Inventory table
     * and delegate them all to one downline user in a single action.
     */
    public function bulkAssignSim(Request $request)
    {
        $request->validate([
            'sim_ids'   => 'required|array|max:50',
            'sim_ids.*' => 'exists:sims,id',
            'user_id'   => 'required|exists:users,id',
        ]);

        $from = Auth::user();
        $targetUser = User::find($request->user_id);
        $sims = Sim::whereIn('id', $request->sim_ids)->get();
        $service = app(SimAssignmentService::class);

        $assignedCount = 0;
        $skipped = 0;

        DB::transaction(function () use ($sims, $from, $targetUser, $service, &$assignedCount, &$skipped) {
            foreach ($sims as $sim) {
                try {
                    $service->assignDown($sim, $from, $targetUser);
                    $assignedCount++;
                } catch (\RuntimeException $e) {
                    $skipped++;
                }
            }
        });

        $message = "Successfully assigned {$assignedCount} SIM number(s) to {$targetUser->first_name} {$targetUser->last_name}.";
        if ($skipped > 0) {
            $message .= " {$skipped} SIM(s) were skipped (no longer eligible).";

            return back()->with('warning', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * A Partner activates a SIM they hold on behalf of a User in their
     * downline. The activation fee is debited from that User's wallet (not
     * the partner's), then the SIM is marked ACTIVATED — which fires the
     * commission payout up the whole chain (partner, coordinator, regional
     * manager). Regional Managers and Coordinators never activate — they
     * only distribute, via partnerAssignSim() above.
     */
    public function activateForDownlineUser(Request $request)
    {
        $request->validate([
            'sim_id'  => 'required|exists:sims,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $partner = Auth::user();
        if ($partner->role !== 'partner') {
            return back()->with('error', 'Only partners can activate a SIM for a downline user.');
        }

        $sim = Sim::find($request->sim_id);
        $targetUser = User::find($request->user_id);

        if ($sim->partner_id !== $partner->id) {
            return back()->with('error', 'You can only activate numbers currently allocated to you.');
        }

        if ($sim->status === SimStatus::ACTIVATED) {
            return back()->with('error', 'This SIM card is already active.');
        }

        if ($targetUser->role !== 'personal' || $targetUser->referred_by !== $partner->id) {
            return back()->with('error', 'You can only activate a SIM for a User in your downline.');
        }

        // Resolve the activation price for the target user's role/category.
        $simService = \App\Models\Service::where('name', 'simcard')->first();
        $serviceField = $simService
            ? \App\Models\ServiceField::where('service_id', $simService->id)->where('field_name', $sim->category)->first()
            : null;
        $payableAmount = $serviceField ? $serviceField->priceForUser($targetUser) : 0.00;

        try {
            DB::transaction(function () use ($sim, $partner, $targetUser, $payableAmount, $simService) {
                // Lock and debit the downline user's wallet — not the partner's.
                $wallet = \App\Models\Wallet::where('user_id', $targetUser->id)->lockForUpdate()->first();
                if (!$wallet || $wallet->balance < $payableAmount) {
                    throw new \Exception("Insufficient wallet balance for {$targetUser->first_name}. They need ₦" . number_format($payableAmount, 2) . ' in their wallet.');
                }

                $oldBalance = $wallet->balance;
                $wallet->decrement('balance', $payableAmount);
                $newBalance = $wallet->balance;

                $ref = 'ACT-' . time() . '-' . rand(1000, 9999);

                \App\Models\Transaction::create([
                    'transaction_ref' => $ref,
                    'user_id'         => $targetUser->id,
                    'amount'          => $payableAmount,
                    'fee'             => 0.00,
                    'net_amount'      => $payableAmount,
                    'description'     => "SIM Card Activation: {$sim->number} (Category: {$sim->category}, Network: " . strtoupper($sim->provider) . "), activated by partner {$partner->first_name} {$partner->last_name}",
                    'type'            => 'debit',
                    'status'          => 'completed',
                    'performed_by'    => $partner->first_name . ' ' . $partner->last_name,
                ]);

                \App\Models\Report::create([
                    'user_id'      => $targetUser->id,
                    'phone_number' => $sim->number,
                    'network'      => $sim->provider,
                    'ref'          => $ref,
                    'amount'       => $payableAmount,
                    'status'       => 'completed',
                    'type'         => 'sim_activation',
                    'description'  => "SIM Card Activation: {$sim->number} (Category: {$sim->category}), activated by partner",
                    'old_balance'  => $oldBalance,
                    'new_balance'  => $newBalance,
                    'service_id'   => $simService ? $simService->id : null,
                ]);

                $sim->update(['user_id' => $targetUser->id]);

                app(SimAssignmentService::class)->activate($sim, $partner);
            });

            return back()->with('success', "Activated {$sim->number} for {$targetUser->first_name} {$targetUser->last_name}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

}
