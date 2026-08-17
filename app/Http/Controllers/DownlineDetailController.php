<?php

namespace App\Http\Controllers;

use App\Mail\KycWelcomeMail;
use App\Models\LeaderboardSettings;
use App\Models\LeaderboardTier;
use App\Models\Sim;
use App\Models\SimHistory;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Models\Wallet;
use App\Repositories\VirtualAccountRepository;
use App\Support\DownlineAuthorization;
use App\Support\RoleHierarchy;
use App\Support\SimStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The team-member detail page reached from Network > Team — Info, Metrics,
 * Tier, and Actions tabs for one specific downline user. Every method here
 * is scoped to a $user route-model-bound target, never Auth::user(), and
 * every entry point is gated by DownlineAuthorization::authorize() (own
 * immediate downline only, or super_admin).
 */
class DownlineDetailController extends Controller
{
    /**
     * Per-role FK on `sims` that scopes "SIMs anywhere in this role's
     * downline cascade" — same map SalesPerformanceController uses for the
     * logged-in user, reused here for an arbitrary target. Agents have no
     * dedicated FK (absent from RoleHierarchy::CHAIN), so they fall back to
     * user_id like personal/business — a purely personal, non-cascading scope.
     */
    private const HOLDER_FK = [
        'regional_manager' => 'regional_manager_id',
        'coordinator' => 'coordinator_id',
        'partner' => 'partner_id',
    ];

    private const ASSIGNED_STATUS = [
        'regional_manager' => SimStatus::ASSIGNED_TO_RM,
        'coordinator' => SimStatus::ASSIGNED_TO_COORDINATOR,
        'partner' => SimStatus::ASSIGNED_TO_PARTNER,
    ];

    public function show(User $user)
    {
        DownlineAuthorization::authorize($user);

        $user->loadMissing('wallet');
        $wallet = $user->wallet ?? Wallet::firstOrCreateForUser($user->id);
        $virtualAccount = VirtualAccount::where('user_id', $user->id)->first();

        $isCatalogRole = array_key_exists($user->role, self::HOLDER_FK);
        $fk = self::HOLDER_FK[$user->role] ?? 'user_id';

        // Personal inventory: what $user holds themselves, not yet handed
        // further down their own chain. Partner tier's "still with them" is
        // user_id === partner_id (see SimAssignmentService::assignDown());
        // RM/Coordinator tiers instead stay at their own ASSIGNED_STATUS
        // until handed down, since those tiers never activate directly.
        $personalQuery = match (true) {
            $user->role === 'partner' => Sim::where('partner_id', $user->id)->whereColumn('user_id', 'partner_id'),
            $isCatalogRole => Sim::where($fk, $user->id)->where('status', self::ASSIGNED_STATUS[$user->role]),
            default => Sim::where('user_id', $user->id),
        };
        $personalTotal = (clone $personalQuery)->count();
        $personalActivated = (clone $personalQuery)->where('status', SimStatus::ACTIVATED)->count();
        $personalConversion = $personalTotal > 0 ? round($personalActivated / $personalTotal * 100) : 0;

        // Team inventory: everywhere in $user's downline cascade (only
        // meaningful for the three tiers that actually cascade further).
        $teamTotal = $teamActivated = $teamConversion = null;
        if ($isCatalogRole) {
            $teamQuery = Sim::where($fk, $user->id);
            $teamTotal = (clone $teamQuery)->count();
            $teamActivated = (clone $teamQuery)->where('status', SimStatus::ACTIVATED)->count();
            $teamConversion = $teamTotal > 0 ? round($teamActivated / $teamTotal * 100) : 0;
        }

        // Downline onboarding — only roles with a next tier can onboard at all.
        $nextRole = RoleHierarchy::nextRole($user->role);
        $totalDownline = $user->referrals()->count();
        $roleBreakdown = $user->referrals()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        // Activation counts (all-time + current leaderboard period), scoped
        // to whichever column represents $user's own holdings.
        $activationScopeColumn = $isCatalogRole ? "sims.$fk" : 'sims.user_id';
        $totalActivations = SimHistory::query()
            ->join('sims', 'sim_histories.sim_id', '=', 'sims.id')
            ->where('sim_histories.to_status', SimStatus::ACTIVATED)
            ->where($activationScopeColumn, $user->id)
            ->count();

        $settings = LeaderboardSettings::current();
        $period = $settings->periodBounds();
        $periodActivations = SimHistory::query()
            ->join('sims', 'sim_histories.sim_id', '=', 'sims.id')
            ->where('sim_histories.to_status', SimStatus::ACTIVATED)
            ->where($activationScopeColumn, $user->id)
            ->whereBetween('sim_histories.created_at', [$period['start'], $period['end']])
            ->count();

        // Tier progress only applies to partners (the leaderboard's only ranked role).
        $currentTier = $nextTier = $progressPercent = null;
        if ($user->role === 'partner') {
            $currentTier = LeaderboardTier::resolveForCount($periodActivations);
            $nextTier = LeaderboardTier::nextTier($periodActivations);
            $progressPercent = $nextTier
                ? min(100, (int) round(($periodActivations / $nextTier->activation_target) * 100))
                : ($currentTier ? 100 : 0);
        }

        return view('network.downline-show', compact(
            'user',
            'wallet',
            'virtualAccount',
            'isCatalogRole',
            'personalTotal',
            'personalActivated',
            'personalConversion',
            'teamTotal',
            'teamActivated',
            'teamConversion',
            'nextRole',
            'totalDownline',
            'roleBreakdown',
            'totalActivations',
            'periodActivations',
            'currentTier',
            'nextTier',
            'progressPercent'
        ));
    }

    /**
     * Upline fills the mandatory profile fields on behalf of a downline user
     * who hasn't cleared the pop-up onboarding modal yet (account_tier = 0
     * — the same signal layouts/app.blade.php checks to force that modal).
     * Mirrors KycController::submit() exactly, minus the "agree to terms"
     * checkbox — that's the account holder's own attestation, not the
     * upline's to give.
     */
    public function completeOnboarding(Request $request, User $user)
    {
        DownlineAuthorization::authorize($user);

        if ($user->account_tier > 0) {
            return back()->with('error', "{$user->first_name} {$user->last_name} has already completed onboarding.");
        }

        $validated = $request->validate([
            'first_name'  => ['required', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'phone'       => ['required', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'state'       => ['required', 'string', 'max:255'],
            'lga'         => ['required', 'string', 'max:255'],
            'address'     => ['required', 'string', 'max:500'],
        ]);

        $user->forceFill($validated + ['account_tier' => 1])->save();

        try {
            Mail::to($user->email)->send(new KycWelcomeMail($user));
        } catch (\Exception $e) {
            Log::warning('KYC welcome email failed to send: ' . $e->getMessage());
        }

        return back()->with('success', "Onboarding completed for {$user->first_name} {$user->last_name}.");
    }

    /**
     * Upline generates a virtual funding account for a downline user, same
     * BVN/NIN flow the user would use themselves (WalletController::createWallet()),
     * scoped to $user instead of Auth::user(). VirtualAccountRepository
     * already accepts an arbitrary user id — no repository changes needed.
     */
    public function generateVirtualAccount(Request $request, User $user)
    {
        DownlineAuthorization::authorize($user);

        if (VirtualAccount::where('user_id', $user->id)->exists()) {
            return back()->with('error', "{$user->first_name} {$user->last_name} already has a virtual account.");
        }

        $validated = $request->validate([
            'phone'         => 'required|string|max:20|unique:users,phone,' . $user->id,
            'identity_type' => 'required|in:bvn,nin',
            'bvn'           => 'required_if:identity_type,bvn|nullable|string|digits:11|unique:users,bvn,' . $user->id,
            'nin'           => 'required_if:identity_type,nin|nullable|string|digits:11|unique:users,nin,' . $user->id,
        ]);

        $identityType = $validated['identity_type'];

        $user->phone = $validated['phone'];
        if ($identityType === 'nin') {
            $user->nin = $validated['nin'];
        } else {
            $user->bvn = $validated['bvn'];
        }
        $user->save();

        $result = app(VirtualAccountRepository::class)->createVirtualAccount($user->id, $identityType);

        if (!is_array($result) || empty($result['success'])) {
            $message = is_array($result) && isset($result['message']) ? $result['message'] : 'Virtual account creation failed. Please try again later.';

            return back()->with('error', $message);
        }

        return back()->with('success', "Virtual account generated for {$user->first_name} {$user->last_name}.");
    }
}
