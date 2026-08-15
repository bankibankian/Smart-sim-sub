<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Support\SimAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a catalog-role upline place/lift a Post No Debit restriction or
 * suspend/reinstate their own immediate downline — and lets super_admin do
 * the same to anyone, regardless of who placed a restriction originally.
 * One shared implementation serves both audiences; see authorize().
 */
class DownlineRestrictionController extends Controller
{
    public function placePnd(Request $request, User $user): RedirectResponse
    {
        $actor = $this->authorize($user);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->placePnd($validated['reason'], $actor->id);

        return back()->with('success', "Post No Debit placed on {$user->first_name} {$user->last_name}'s account.");
    }

    public function liftPnd(Request $request, User $user): RedirectResponse
    {
        $this->authorize($user);

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->liftPnd();

        return back()->with('success', "Post No Debit lifted from {$user->first_name} {$user->last_name}'s account.");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $actor = $this->authorize($user);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $user->suspend($validated['reason'], $actor->id);

        return back()->with('success', "{$user->first_name} {$user->last_name} has been suspended.");
    }

    public function reinstate(Request $request, User $user): RedirectResponse
    {
        $this->authorize($user);

        $user->reinstate();

        return back()->with('success', "{$user->first_name} {$user->last_name} has been reinstated.");
    }

    /**
     * super_admin may act on anyone; a catalog-role upline only on their own
     * immediate (direct referred_by) downline. Returns the acting user.
     */
    private function authorize(User $target): User
    {
        $actor = Auth::user();
        if (!$actor) {
            abort(403);
        }

        $isImmediateDownline = SimAccess::canBrowseCatalog($actor)
            && $actor->referrals()->where('id', $target->id)->exists();

        if (!$actor->hasRole('super_admin') && !$isImmediateDownline) {
            abort(403, 'You can only restrict your own immediate downline.');
        }

        return $actor;
    }
}
