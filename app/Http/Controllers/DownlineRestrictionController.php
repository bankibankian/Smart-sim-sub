<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a super_admin place/lift a Post No Debit restriction, a
 * partial-amount Lien, or suspend/reinstate any user. Restricting an
 * account is admin-only — uplines (partners, coordinators, etc.) no longer
 * have this capability, on the team-member detail page or otherwise.
 */
class DownlineRestrictionController extends Controller
{
    /** Aborts 403 on failure. Returns the acting admin on success. */
    private function authorizeAdmin(): User
    {
        $actor = Auth::user();
        if (!$actor || !$actor->hasRole('super_admin')) {
            abort(403, 'Only administrators can restrict user accounts.');
        }

        return $actor;
    }

    public function placePnd(Request $request, User $user): RedirectResponse
    {
        $actor = $this->authorizeAdmin();

        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->placePnd($validated['reason'], $actor->id);

        return back()->with('success', "Post No Debit placed on {$user->first_name} {$user->last_name}'s account.");
    }

    public function liftPnd(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->liftPnd();

        return back()->with('success', "Post No Debit lifted from {$user->first_name} {$user->last_name}'s account.");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $actor = $this->authorizeAdmin();

        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $user->suspend($validated['reason'], $actor->id);

        return back()->with('success', "{$user->first_name} {$user->last_name} has been suspended.");
    }

    public function reinstate(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $user->reinstate();

        return back()->with('success', "{$user->first_name} {$user->last_name} has been reinstated.");
    }

    public function placeLien(Request $request, User $user): RedirectResponse
    {
        $actor = $this->authorizeAdmin();

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
        $this->authorizeAdmin();

        $wallet = Wallet::firstOrCreateForUser($user->id);
        $wallet->liftLien();

        return back()->with('success', "Lien lifted from {$user->first_name} {$user->last_name}'s account.");
    }
}
