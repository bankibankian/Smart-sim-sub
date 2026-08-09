<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show registration page
     */
    public function create(Request $request): View
    {
        $referrer = null;
        if ($request->filled('ref')) {
            $referrer = User::where('referral_code', $request->string('ref'))->first();
        }

        return view('auth.register', ['referrer' => $referrer]);
    }

    /**
     * Handle registration
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'terms'    => ['accepted'],
        ], [
            'terms.accepted' => 'You must accept the Terms & Conditions to register.',
        ]);

        DB::beginTransaction();

        try {
            // Resolve the referring user (if a valid referral code was passed via ?ref=)
            $referrer = null;
            if ($request->filled('ref')) {
                $referrer = User::where('referral_code', $request->string('ref'))->first();
            }

            // Create user
            $user = User::create([
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'role'          => 'personal',
                'status'        => 'active',
                'referral_code' => strtoupper(\Illuminate\Support\Str::random(8)),
                'referred_by'   => $referrer?->id,
                'limit'         => 20000.00,
            ]);

            // Create wallet with all required columns
            Wallet::create([
                'user_id'        => $user->id,
                'balance'        => 0.00,
                'bonus'          => 0.00,
                'hold_amount'    => 0.00,
                'total_credited' => 0.00,
                'total_debited'  => 0.00,
                'wallet_number'  => 'WLT' . str_pad($user->id, 7, '0', STR_PAD_LEFT),
                'currency'       => 'NGN',
                'daily_limit'    => 100000.00,
                'monthly_limit'  => 1000000.00,
                'status'         => 'active',
                'is_locked'      => false,
                'last_activity'  => now(),
            ]);

            DB::commit();

            // Log the user in first, then send OTP for email verification
            event(new Registered($user));
            Auth::login($user);
            $request->session()->regenerate();

            // Generate and send OTP — redirect to verification page
            $user->generateOtp();
            $user->sendOtpEmail();

            return redirect()->route('verification.notice')
                ->with('status', 'verification-otp-sent')
                ->with('info', 'A 6-digit verification code has been sent to ' . $user->email);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }
}
