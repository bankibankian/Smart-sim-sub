<?php

namespace App\Support;

use App\Models\User;

/**
 * Role gating for the consumer utility pages (Buy Airtime, Buy Data,
 * Verification Services). Individual end customers (personal, business) and
 * agents buy all three for themselves through this storefront flow; partners
 * additionally get Verification only, as a paid side-service they offer
 * their downline/customers. Coordinators, regional managers, and back-office
 * roles (staff, checker, super_admin) manage the catalog/platform instead
 * and get none of these.
 */
class UtilityAccess
{
    public const ALLOWED_ROLES = ['personal', 'business', 'agent'];

    public static function canUse(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }

    /** Verification Services get one extra carve-out beyond canUse(): partners. */
    public static function canVerify(User $user): bool
    {
        return self::canUse($user) || $user->role === 'partner';
    }
}
