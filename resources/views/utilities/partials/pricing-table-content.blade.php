<div class="overflow-x-auto max-h-[420px] overflow-y-auto pr-1">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                <th class="py-3 px-2">Size</th>
                <th class="py-3 px-2">Type</th>
                <th class="py-3 px-2 text-right">Price</th>
                <th class="py-3 px-2 text-center">Validity</th>
                <th class="py-3 px-2 text-right">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100/60 text-xs">
            @forelse($plans as $plan)
                <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                    <td class="py-3.5 px-2 font-bold text-slate-800">{{ $plan->size }}</td>
                    <td class="py-3.5 px-2">
                        <span class="inline-block text-[9px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 uppercase">{{ $plan->plan_type }}</span>
                    </td>
                    <td class="py-3.5 px-2 text-right font-extrabold text-emerald-600 font-display">
                        ₦{{ number_format($plan->calculatePriceForRole(auth()->user()->role ?? 'personal'), 2) }}
                    </td>
                    <td class="py-3.5 px-2 text-center text-slate-400 font-semibold">{{ $plan->validity }} Days</td>
                    <td class="py-3.5 px-2 text-right">
                        <button type="button" 
                                onclick="autoSelectPlan('{{ $plan->network }}', '{{ $plan->plan_type }}', '{{ $plan->data_id }}')" 
                                class="inline-flex items-center gap-1 py-1.5 px-3 bg-indigo-50 hover:bg-[#0056D2] hover:text-white text-[#0056D2] font-bold rounded-lg text-[10px] transition-all duration-150">
                            Select
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400">No plans available for this network.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
