<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivationBonusSettings;
use App\Models\SmeData;
use Illuminate\Http\Request;

class SmePlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SmeData::query();

        // Search by Data ID or Size
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('data_id', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%");
            });
        }

        // Filter by Network
        if ($request->filled('network')) {
            $query->where('network', $request->network);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        if ($request->sort == 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $plans = $query->paginate(10)->appends(request()->query());

        // Calculate stats
        $totalPlansCount = SmeData::count();
        $activePlansCount = SmeData::where('status', 'enabled')->count();
        $disabledPlansCount = SmeData::where('status', 'disabled')->count();

        $bonusSettings = ActivationBonusSettings::current();
        $bonusEligiblePlans = SmeData::where('status', 'enabled')->orderBy('network')->orderBy('size')->get();

        return view('admin.sme-plans.index', compact(
            'plans',
            'totalPlansCount',
            'activePlansCount',
            'disabledPlansCount',
            'bonusSettings',
            'bonusEligiblePlans'
        ));
    }

    /**
     * Update the silent post-activation data bonus: on/off and which plan to grant.
     */
    public function updateActivationBonus(Request $request)
    {
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'sme_data_id' => ['nullable', 'exists:sme_data,id'],
        ]);

        ActivationBonusSettings::current()->update([
            'is_active' => $request->boolean('is_active'),
            'sme_data_id' => $validated['sme_data_id'] ?? null,
        ]);

        return back()->with('success', 'Activation bonus settings updated.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_id' => 'required|string|unique:sme_data,data_id',
            'network' => 'required|string|in:MTN,GLO,AIRTEL,9MOBILE',
            'plan_type' => 'required|string',
            'personal_price' => 'required|numeric|min:0',
            'agent_price' => 'required|numeric|min:0',
            'partner_price' => 'required|numeric|min:0',
            'business_price' => 'required|numeric|min:0',
            'size' => 'required|string|max:255',
            'validity' => 'required|string|max:100',
            'status' => 'required|string|in:enabled,disabled',
        ]);

        SmeData::create($validated);

        return back()->with('success', 'SME Data Plan created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SmeData $plan)
    {
        $validated = $request->validate([
            'data_id' => 'required|string|unique:sme_data,data_id,' . $plan->id,
            'network' => 'required|string|in:MTN,GLO,AIRTEL,9MOBILE',
            'plan_type' => 'required|string',
            'personal_price' => 'required|numeric|min:0',
            'agent_price' => 'required|numeric|min:0',
            'partner_price' => 'required|numeric|min:0',
            'business_price' => 'required|numeric|min:0',
            'size' => 'required|string|max:255',
            'validity' => 'required|string|max:100',
            'status' => 'required|string|in:enabled,disabled',
        ]);

        $plan->update($validated);

        return back()->with('success', 'SME Data Plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmeData $plan)
    {
        $plan->delete();

        return back()->with('success', 'SME Data Plan deleted successfully.');
    }
}
