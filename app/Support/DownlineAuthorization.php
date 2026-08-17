<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Shared "can this actor act on this specific downline user" rule: a
 * catalog-role upline may only act on their own immediate (direct
 * referred_by) downline; super_admin may act on anyone. Used by every
 * controller that lets an upline take an action against one of their
 * downline users (restrictions, invite management, the team-member detail
 * page) so the rule lives in exactly one place.
 */
class DownlineAuthorization
{
    /** Aborts 403 on failure. Returns the acting user on success. */
    public static function authorize(User $target): User
    {
        $actor = Auth::user();
        if (!$actor) {
            abort(403);
        }

        $isImmediateDownline = SimAccess::canBrowseCatalog($actor)
            && $actor->referrals()->where('id', $target->id)->exists();

        if (!$actor->hasRole('super_admin') && !$isImmediateDownline) {
            abort(403, 'You can only act on your own immediate downline.');
        }

        return $actor;
    }
}
