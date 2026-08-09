<?php

namespace App\Support;

use App\Models\User;

/**
 * Badge tier + progress based on total downline size, plus this week's new
 * signups — purely motivational, not tied to any payout. Shared by anywhere
 * that shows a partner their referral progress (currently the Leaderboard page).
 */
class ReferralGamification
{
    private const BADGE_TIERS = [
        ['threshold' => 5, 'label' => 'Bronze Recruiter'],
        ['threshold' => 20, 'label' => 'Silver Recruiter'],
        ['threshold' => 50, 'label' => 'Gold Recruiter'],
        ['threshold' => 100, 'label' => 'Platinum Recruiter'],
    ];

    public static function build(User $user): array
    {
        $totalDownline = $user->referrals()->count();
        $weeklyReferrals = $user->referrals()->where('created_at', '>=', now()->startOfWeek())->count();

        $currentBadge = null;
        $nextBadge = null;
        foreach (self::BADGE_TIERS as $tier) {
            if ($totalDownline >= $tier['threshold']) {
                $currentBadge = $tier;
            } elseif (!$nextBadge) {
                $nextBadge = $tier;
            }
        }

        $prevThreshold = $currentBadge['threshold'] ?? 0;
        $progressPercent = $nextBadge
            ? min(100, (int) round((($totalDownline - $prevThreshold) / ($nextBadge['threshold'] - $prevThreshold)) * 100))
            : ($currentBadge ? 100 : 0);

        return [
            'totalDownline' => $totalDownline,
            'weeklyReferrals' => $weeklyReferrals,
            'currentBadge' => $currentBadge,
            'nextBadge' => $nextBadge,
            'progressPercent' => $progressPercent,
        ];
    }
}
