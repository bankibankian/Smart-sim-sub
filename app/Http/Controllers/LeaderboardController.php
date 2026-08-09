<?php

namespace App\Http\Controllers;

use App\Models\LeaderboardSettings;
use App\Models\LeaderboardTier;
use App\Models\SimHistory;
use App\Models\User;
use App\Support\ReferralGamification;
use App\Support\SimStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Partner activation leaderboard: ranks partners by SIM activations
     * within the admin-configured period, against admin-configured tiers.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }

        if (!in_array($user->role, ['partner', 'super_admin'], true)) {
            abort(403, 'The leaderboard is only available to partners.');
        }

        $settings = LeaderboardSettings::current();
        $hasTiers = LeaderboardTier::where('status', 'active')->exists();
        $referralLink = url('/register?ref=' . $user->referral_code);
        $referralGamification = $user->role === 'partner' ? ReferralGamification::build($user) : null;

        if (!$settings->is_active) {
            return view('leaderboard.index', [
                'user' => $user,
                'settings' => $settings,
                'active' => false,
                'referralLink' => $referralLink,
                'referralGamification' => $referralGamification,
            ]);
        }

        $period = $settings->periodBounds();

        // Every partner's activation count within the current period, in one query.
        $counts = SimHistory::query()
            ->join('sims', 'sim_histories.sim_id', '=', 'sims.id')
            ->where('sim_histories.to_status', SimStatus::ACTIVATED)
            ->whereBetween('sim_histories.created_at', [$period['start'], $period['end']])
            ->whereNotNull('sims.partner_id')
            ->select('sims.partner_id', DB::raw('count(*) as activations'))
            ->groupBy('sims.partner_id')
            ->pluck('activations', 'sims.partner_id');

        $partners = User::where('role', 'partner')->orderBy('first_name')->get();

        $rankings = $partners
            ->map(function ($partner) use ($counts) {
                $count = (int) ($counts[$partner->id] ?? 0);

                return [
                    'user' => $partner,
                    'activations' => $count,
                    'tier' => LeaderboardTier::resolveForCount($count),
                ];
            })
            ->sortByDesc('activations')
            ->values()
            ->map(function ($row, $index) {
                $row['rank'] = $index + 1;

                return $row;
            });

        $myRow = $rankings->firstWhere('user.id', $user->id);

        $totalActivations = null;
        $periodActivations = null;
        $currentTier = null;
        $nextTier = null;
        $progressPercent = null;

        if ($user->role === 'partner') {
            $totalActivations = SimHistory::query()
                ->join('sims', 'sim_histories.sim_id', '=', 'sims.id')
                ->where('sim_histories.to_status', SimStatus::ACTIVATED)
                ->where('sims.partner_id', $user->id)
                ->count();

            $periodActivations = $myRow['activations'] ?? 0;
            $currentTier = LeaderboardTier::resolveForCount($periodActivations);
            $nextTier = LeaderboardTier::nextTier($periodActivations);
            $progressPercent = $nextTier
                ? min(100, (int) round(($periodActivations / $nextTier->activation_target) * 100))
                : ($currentTier ? 100 : 0);
        }

        return view('leaderboard.index', compact(
            'user',
            'settings',
            'period',
            'hasTiers',
            'rankings',
            'myRow',
            'totalActivations',
            'periodActivations',
            'currentTier',
            'nextTier',
            'progressPercent',
            'referralLink',
            'referralGamification'
        ) + ['active' => true]);
    }
}
