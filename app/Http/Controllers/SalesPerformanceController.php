<?php

namespace App\Http\Controllers;

use App\Models\CommissionEarning;
use App\Models\Sim;
use App\Support\SimAccess;
use App\Support\SimStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesPerformanceController extends Controller
{
    /**
     * A SIM held N+ days without being activated is flagged as dormant —
     * simple, easy-to-tune constant rather than a new admin setting for one number.
     */
    public const DORMANT_DAYS = 7;

    /**
     * Per-role FK on `sims` that scopes "SIMs this role currently holds or
     * has distributed downline". Agents have no dedicated FK (absent from
     * RoleHierarchy::CHAIN entirely — they self-hold/self-activate via
     * user_id), so they're absent here and get a personal framing below.
     */
    private const HOLDER_FK = [
        'regional_manager' => 'regional_manager_id',
        'coordinator'       => 'coordinator_id',
        'partner'           => 'partner_id',
    ];

    private const ASSIGNED_STATUS = [
        'regional_manager' => SimStatus::ASSIGNED_TO_RM,
        'coordinator'       => SimStatus::ASSIGNED_TO_COORDINATOR,
        'partner'           => SimStatus::ASSIGNED_TO_PARTNER,
    ];

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }
        if (!SimAccess::canBrowseCatalog($user)) {
            abort(403, 'Sales Performance is only available to Regional Managers, Coordinators, Partners, and Agents.');
        }

        // Agents have no downline FK — personal mode scopes by their own
        // held SIMs (user_id) instead of a downline holder column.
        $fk = self::HOLDER_FK[$user->role] ?? 'user_id';
        $isPersonal = $fk === 'user_id';
        $assignedStatus = self::ASSIGNED_STATUS[$user->role] ?? SimStatus::ASSIGNED_TO_PARTNER;

        $simsQuery = Sim::where($fk, $user->id);
        $totalSims = (clone $simsQuery)->count();
        $activatedSims = (clone $simsQuery)->where('status', SimStatus::ACTIVATED)->count();
        $conversionRate = $totalSims > 0 ? round($activatedSims / $totalSims * 100) : 0;

        $unactivatedQuery = (clone $simsQuery)->where('status', $assignedStatus)->orderBy('created_at');
        if (!$isPersonal) {
            $unactivatedQuery->with('user');
        }
        $unactivatedSims = $unactivatedQuery->limit(20)->get()->map(function (Sim $sim) use ($isPersonal) {
            $daysHeld = (int) $sim->created_at->diffInDays(now());

            return [
                'sim'        => $sim,
                'holder'     => $isPersonal ? null : $sim->user,
                'days_held'  => $daysHeld,
                'is_dormant' => $daysHeld >= self::DORMANT_DAYS,
            ];
        });
        $dormantCount = $unactivatedSims->where('is_dormant', true)->count();

        $thisWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY)->toDateString();

        $commissionThisWeek = (float) CommissionEarning::where('user_id', $user->id)
            ->where('period_week', $thisWeekStart)
            ->sum('amount');
        $commissionLastWeek = (float) CommissionEarning::where('user_id', $user->id)
            ->where('period_week', $lastWeekStart)
            ->sum('amount');

        $commissionTrendPct = $commissionLastWeek > 0
            ? round((($commissionThisWeek - $commissionLastWeek) / $commissionLastWeek) * 100)
            : ($commissionThisWeek > 0 ? 100 : 0);

        // Last 6 Monday-starts (oldest first), for a small trend chart.
        $weeklyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $amount = (float) CommissionEarning::where('user_id', $user->id)
                ->where('period_week', $weekStart->toDateString())
                ->sum('amount');

            $weeklyTrend[] = [
                'label'  => $weekStart->format('M d'),
                'amount' => $amount,
            ];
        }

        return view('sales-performance.index', compact(
            'user',
            'isPersonal',
            'totalSims',
            'activatedSims',
            'conversionRate',
            'unactivatedSims',
            'dormantCount',
            'commissionThisWeek',
            'commissionLastWeek',
            'commissionTrendPct',
            'weeklyTrend'
        ));
    }
}
