<?php

namespace App\Support;

use App\Models\User;

/**
 * Single source of truth for the onboarding / distribution chain:
 * Regional Manager -> Coordinator -> Partner -> User (personal).
 *
 * Each role may only onboard, or assign a SIM to, the role directly
 * beneath it in this chain.
 */
class RoleHierarchy
{
    public const CHAIN = [
        'regional_manager' => 'coordinator',
        'coordinator' => 'partner',
        'partner' => 'personal',
    ];

    /** Roles that bypass the hierarchy for admin operations. */
    public const BYPASS_ROLES = ['super_admin', 'staff'];

    /** The role directly beneath the given role in the chain, or null if it's the base level. */
    public static function nextRole(string $role): ?string
    {
        return self::CHAIN[$role] ?? null;
    }

    /** Can $onboarder create/onboard an account with role $targetRole? */
    public static function canOnboard(User $onboarder, string $targetRole): bool
    {
        if (in_array($onboarder->role, self::BYPASS_ROLES, true)) {
            return true;
        }

        return self::nextRole($onboarder->role) === $targetRole;
    }

    /** Roles that have a subordinate role beneath them and can therefore onboard someone. */
    public static function onboardingRoles(): array
    {
        return array_keys(self::CHAIN);
    }
}
