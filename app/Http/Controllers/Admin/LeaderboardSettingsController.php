<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaderboardSettings;
use App\Models\LeaderboardTier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaderboardSettingsController extends Controller
{
    /**
     * Leaderboard settings + tier management page.
     */
    public function index()
    {
        $settings = LeaderboardSettings::current();
        $tiers = LeaderboardTier::orderBy('activation_target')->get();

        return view('admin.leaderboard.index', compact('settings', 'tiers'));
    }

    /**
     * Update the active flag and counting period.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'period_type' => ['required', 'in:weekly,custom'],
            'period_start' => ['nullable', 'date', 'required_if:period_type,custom'],
            'period_end' => ['nullable', 'date', 'required_if:period_type,custom', 'after_or_equal:period_start'],
        ]);

        LeaderboardSettings::current()->update([
            'is_active' => $request->boolean('is_active'),
            'period_type' => $request->period_type,
            'period_start' => $request->period_type === 'custom' ? $request->period_start : null,
            'period_end' => $request->period_type === 'custom' ? $request->period_end : null,
        ]);

        return back()->with('success', 'Leaderboard settings updated.');
    }

    /**
     * Add a new activation target -> bonus tier.
     */
    public function storeTier(Request $request)
    {
        $request->validate([
            'activation_target' => ['required', 'integer', 'min:1', 'unique:leaderboard_tiers,activation_target'],
            'bonus_amount' => ['required', 'numeric', 'min:0'],
        ]);

        LeaderboardTier::create([
            'activation_target' => $request->activation_target,
            'bonus_amount' => $request->bonus_amount,
            'status' => 'active',
        ]);

        return back()->with('success', 'Tier added.');
    }

    /**
     * Update an existing tier.
     */
    public function updateTier(Request $request, LeaderboardTier $tier)
    {
        $request->validate([
            'activation_target' => ['required', 'integer', 'min:1', Rule::unique('leaderboard_tiers', 'activation_target')->ignore($tier->id)],
            'bonus_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $tier->update($request->only(['activation_target', 'bonus_amount', 'status']));

        return back()->with('success', 'Tier updated.');
    }

    /**
     * Remove a tier.
     */
    public function destroyTier(LeaderboardTier $tier)
    {
        $tier->delete();

        return back()->with('success', 'Tier removed.');
    }
}
