<?php

namespace App\Support;

/**
 * The 7 lifecycle states a SIM can be in as it cascades down the
 * Regional Manager -> Coordinator -> Partner -> User hierarchy and gets activated.
 *
 * Kept as plain string constants (not an Eloquent cast) so `sims.status`
 * stays a raw string attribute, matching every existing comparison
 * across the SIM controllers and Blade views.
 */
class SimStatus
{
    public const ASSIGNED_TO_RM = 'ASSIGNED_TO_RM';
    public const ASSIGNED_TO_COORDINATOR = 'ASSIGNED_TO_COORDINATOR';
    public const ASSIGNED_TO_PARTNER = 'ASSIGNED_TO_PARTNER';
    public const ACTIVATED = 'ACTIVATED';
    public const DEACTIVATED = 'DEACTIVATED';
    public const SUSPENDED = 'SUSPENDED';
    public const UNASSIGNED = 'UNASSIGNED';

    public static function all(): array
    {
        return [
            self::ASSIGNED_TO_RM,
            self::ASSIGNED_TO_COORDINATOR,
            self::ASSIGNED_TO_PARTNER,
            self::ACTIVATED,
            self::DEACTIVATED,
            self::SUSPENDED,
            self::UNASSIGNED,
        ];
    }
}
