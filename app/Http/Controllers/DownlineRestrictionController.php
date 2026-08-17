<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Support\DownlineAuthorization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lets a catalog-role upline place/lift a Post No Debit restriction, a
 * partial-amount Lien, or suspend/reinstate their own immediate downline —
 * and lets super_admin do the same to anyone, regardless of who placed a
 * restriction originally. See DownlineAuthorization::authorize().
 */
class DownlineRestrictionController extends Controller
{
    public function placePnd(Request $request, User $user): RedirectResponse
    {
        $actor = DownlineAuthorization::authorize($user);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->placePnd($validated['reason'], $actor->id);

        return back()->with('success', "Post No Debit placed on {$user->first_name} {$user->last_name}'s account.");
    }

    public function liftPnd(Request $request, User $user): RedirectResponse
    {
        DownlineAuthorization::authorize($user);

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->liftPnd();

        return back()->with('success', "Post No Debit lifted from {$user->first_name} {$user->last_name}'s account.");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $actor = DownlineAuthorization::authorize($user);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $user->suspend($validated['reason'], $actor->id);

        return back()->with('success', "{$user->first_name} {$user->last_name} has been suspended.");
    }

    public function reinstate(Request $request, User $user): RedirectResponse
    {
        DownlineAuthorization::authorize($user);

        $user->reinstate();

        return back()->with('success', "{$user->first_name} {$user->last_name} has been reinstated.");
    }

    public function placeLien(Request $request, User $user): RedirectResponse
    {
        $actor = DownlineAuthorization::authorize($user);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->placeLien((float) $validated['amount'], $validated['reason'], $actor->id);

        return back()->with('success', "₦" . number_format($validated['amount'], 2) . " lien placed on {$user->first_name} {$user->last_name}'s account.");
    }

    public function liftLien(Request $request, User $user): RedirectResponse
    {
        DownlineAuthorization::authorize($user);

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->liftLien();

        return back()->with('success', "Lien lifted from {$user->first_name} {$user->last_name}'s account.");
    }
}
