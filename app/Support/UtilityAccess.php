<?php

namespace App\Support;

use App\Models\User;

/**
 * Role gating for the consumer utility pages (Buy Airtime, Buy Data,
 * Verification Services). Only individual end customers buy these for
 * themselves through this storefront flow — resellers (regional_manager,
 * coordinator, partner, agent) manage the SIM catalog/hierarchy instead, and
 * back-office roles (staff, checker, super_admin) manage the platform.
 */
class UtilityAccess
{
    public const ALLOWED_ROLES = ['personal', 'business'];

    public static function canUse(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }
}
