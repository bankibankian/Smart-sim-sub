<?php

namespace App\Http\Controllers;

use App\Mail\OnboardingInviteMail;
use App\Models\User;
use App\Support\DownlineAuthorization;
use App\Support\RoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class NetworkController extends Controller
{
    /**
     * "My Network" — the downline this user has onboarded (directly), plus
     * their public sign-up link and, if their role can onboard a subordinate,
     * a shortcut into the invite-downline flow.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }

        $nextRole = RoleHierarchy::nextRole($user->role);

        $totalReferrals = $user->referrals()->count();
        $activeReferrals = $user->referrals()->where('status', 'active')->count();
        $pendingInvites = $user->referrals()->where('status', 'invited')->count();

        $roleBreakdown = $user->referrals()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        // Invited = account created but not yet activated (no usable
        // password yet, see OnboardingController::sendInvite()). Team =
        // everyone else, including already-suspended members so the upline
        // can see and reinstate them from the same place.
        $invited = $user->referrals()
            ->where('status', 'invited')
            ->latest()
            ->paginate(10, ['*'], 'invited_page');

        $team = $user->referrals()
            ->where('status', '!=', 'invited')
            ->with('wallet')
            ->latest()
            ->paginate(10, ['*'], 'team_page');

        return view('network.index', compact(
            'user',
            'nextRole',
            'totalReferrals',
            'activeReferrals',
            'pendingInvites',
            'roleBreakdown',
            'invited',
            'team'
        ));
    }

    /**
     * Regional Manager / Coordinator / Partner look up a phone number to see
     * whether it belongs to a self-onboarded (unclaimed) User account they
     * can claim into their network.
     */
    public function checkClaim(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }

        if (!in_array($user->role, RoleHierarchy::onboardingRoles(), true)) {
            abort(403, 'Only Regional Managers, Coordinators, and Partners can claim downline members.');
        }

        $phone = trim((string) $request->query('phone', ''));

        if (!preg_match('/^[0-9]{11}$/', $phone)) {
            return back()->with('claim_result', [
                'success' => false,
                'message' => 'Enter a valid 11-digit phone number.',
            ]);
        }

        $target = User::where('phone', $phone)->first();

        if (!$target) {
            return back()->with('claim_result', [
                'success' => false,
                'message' => 'No account found with that phone number.',
            ]);
        }

        if ($target->id === $user->id) {
            return back()->with('claim_result', [
                'success' => false,
                'message' => 'You cannot claim your own account.',
            ]);
        }

        if ($target->role !== 'personal') {
            return back()->with('claim_result', [
                'success' => false,
                'message' => 'This number does not belong to a self-onboarded User account.',
            ]);
        }

        if (!is_null($target->referred_by)) {
            return back()->with('claim_result', [
                'success' => false,
                'message' => 'This account has already been claimed by someone else.',
            ]);
        }

        $nextRole = RoleHierarchy::nextRole($user->role);
        $roleLabel = $nextRole === 'personal' ? 'User' : str_replace('_', ' ', (string) $nextRole);

        return back()->with('claim_result', [
            'success'   => true,
            'user_id'   => $target->id,
            'name'      => trim($target->first_name . ' ' . $target->last_name) ?: $target->email,
            'email'     => $target->email,
            'phone'     => $target->phone,
            'role_label' => $roleLabel,
        ]);
    }

    /**
     * Regional Manager / Coordinator / Partner claims a validated,
     * self-onboarded (unclaimed) User into their network. Claiming promotes
     * the claimed account to the claimer's immediate downline role — e.g. a
     * Regional Manager claiming a self-onboarded signup makes them a
     * Coordinator; a Partner claiming one leaves them as a personal User.
     */
    public function claim(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }

        $nextRole = RoleHierarchy::nextRole($user->role);
        if (!$nextRole) {
            abort(403, 'Only Regional Managers, Coordinators, and Partners can claim downline members.');
        }

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $claimed = DB::transaction(function () use ($request, $user, $nextRole) {
            $target = User::where('id', $request->user_id)->lockForUpdate()->first();

            if (!$target || $target->role !== 'personal' || !is_null($target->referred_by) || $target->id === $user->id) {
                return null;
            }

            $target->update([
                'referred_by' => $user->id,
                'role' => $nextRole,
            ]);

            return $target;
        });

        if (!$claimed) {
            return back()->with('error', 'This account can no longer be claimed — it may have just been claimed by someone else.');
        }

        $roleLabel = $nextRole === 'personal' ? 'User' : str_replace('_', ' ', $nextRole);

        return back()->with('success', "Claimed {$claimed->first_name} {$claimed->last_name} into your network as your {$roleLabel}.");
    }

    /**
     * Upline corrects a typo'd invite email before the invitee has accepted.
     * Phone is intentionally not editable here — the invite is keyed to the
     * account row created by OnboardingController::sendInvite(), and phone
     * edits are out of scope for now.
     */
    public function updateInvited(Request $request, User $user)
    {
        DownlineAuthorization::authorize($user);

        if ($user->status !== 'invited') {
            return back()->with('error', 'This invite has already been accepted.');
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update(['email' => $validated['email']]);

        return back()->with('success', "Invite email updated to {$user->email}.");
    }

    /**
     * Re-sends the same onboarding-invite email with a freshly-generated
     * 3-day signed accept link (the original link may have expired or never
     * arrived) — no separate invite-tracking record exists, so this simply
     * repeats what OnboardingController::sendInvite() already does.
     */
    public function resendInvite(User $user)
    {
        $actor = DownlineAuthorization::authorize($user);

        if ($user->status !== 'invited') {
            return back()->with('error', 'This invite has already been accepted.');
        }

        $acceptUrl = URL::temporarySignedRoute('onboarding.accept', now()->addDays(3), ['user' => $user->id]);

        Mail::to($user->email)->send(new OnboardingInviteMail($user, $actor, $acceptUrl));

        return back()->with('success', "Invite resent to {$user->email}.");
    }
}
