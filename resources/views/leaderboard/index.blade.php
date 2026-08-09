<x-app-layout>
    <title>SmartSIM - Leaderboard</title>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold font-display text-slate-800 flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <i data-lucide="trophy" class="w-5 h-5"></i>
                    </div>
                    Leaderboard
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    @if ($active ?? false)
                        Ranked by SIM activations
                        {{ ($settings->period_type ?? 'weekly') === 'weekly' ? 'this week' : 'in the current period' }}
                        ({{ $period['start']->format('M d') }} &ndash; {{ $period['end']->format('M d, Y') }}).
                    @else
                        The leaderboard is not currently running.
                    @endif
                </p>
            </div>

            @if ($user->hasRole('super_admin'))
                <a href="{{ route('admin.leaderboard.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-700 font-semibold text-xs shadow-sm transition-all hover:bg-slate-50 hover:border-slate-400 active:scale-[0.98] whitespace-nowrap">
                    <i data-lucide="settings" class="w-3.5 h-3.5 text-slate-500"></i>
                    Configure Tiers
                </a>
            @endif
        </div>

        @unless ($active ?? false)
            <x-card padding="p-10">
                <div class="text-center">
                    <div class="bg-slate-50 rounded-2xl w-14 h-14 flex items-center justify-center mb-3 mx-auto border border-slate-100">
                        <i data-lucide="power-off" class="w-6 h-6 text-slate-400"></i>
                    </div>
                    <h6 class="font-bold text-slate-800 text-sm">Leaderboard is currently off</h6>
                    <p class="text-xs text-slate-400 mt-1 max-w-[320px] mx-auto">
                        @if ($user->hasRole('super_admin'))
                            Turn it on from the settings page to start tracking activations and tiers.
                        @else
                            Check back later — an admin hasn't switched it on yet.
                        @endif
                    </p>
                </div>
            </x-card>
        @else
            @if ($user->role === 'partner')
                <!-- Personal Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-card padding="p-5" class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Total Activations</p>
                            <p class="text-lg font-bold font-display text-slate-800">{{ number_format($totalActivations) }}</p>
                        </div>
                    </x-card>
                    <x-card padding="p-5" class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
                            <i data-lucide="zap" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">{{ ($settings->period_type ?? 'weekly') === 'weekly' ? "This Week's" : "This Period's" }} Activations</p>
                            <p class="text-lg font-bold font-display text-slate-800">{{ number_format($periodActivations) }}</p>
                        </div>
                    </x-card>
                </div>

                <x-card padding="p-6">
                    <div class="flex items-center justify-between gap-4 flex-wrap mb-4">
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Current Tier</p>
                            @if ($currentTier)
                                <p class="text-lg font-bold font-display text-slate-800">
                                    {{ $currentTier->activation_target }}+ activations
                                    <span class="text-primary">&mdash; ₦{{ number_format($currentTier->bonus_amount, 2) }}</span>
                                </p>
                            @else
                                <p class="text-lg font-bold font-display text-slate-400">No tier yet</p>
                            @endif
                        </div>
                        @if ($myRow)
                            <span class="px-3 py-1 text-xs font-extrabold bg-primary/10 text-primary border border-primary/10 rounded-full uppercase tracking-wider">
                                Rank #{{ $myRow['rank'] }}
                            </span>
                        @endif
                    </div>

                    @if (!$hasTiers)
                        <p class="text-xs text-slate-400">Admin hasn't configured any tiers yet — check back later.</p>
                    @elseif ($nextTier)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-600">Progress to {{ $nextTier->activation_target }} activations (₦{{ number_format($nextTier->bonus_amount, 2) }})</span>
                                <span class="font-bold text-primary">{{ $progressPercent }}%</span>
                            </div>
                            <div class="w-full bg-primary/10 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            <p class="text-xs text-slate-400">
                                {{ max(0, $nextTier->activation_target - $periodActivations) }} more activation(s) to reach the next tier.
                            </p>
                        </div>
                    @else
                        <p class="text-xs text-emerald-600 font-semibold flex items-center gap-1.5">
                            <i data-lucide="party-popper" class="w-3.5 h-3.5"></i>
                            You've reached the highest tier available.
                        </p>
                    @endif
                </x-card>
            @endif

            <!-- Rankings -->
            <x-card padding="p-6">
                <h3 class="text-sm font-semibold text-slate-800 font-display pb-3 border-b border-slate-100 mb-4">
                    Partner Rankings
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                                <th class="py-2.5">Rank</th>
                                <th class="py-2.5">Partner</th>
                                <th class="py-2.5">Activations</th>
                                <th class="py-2.5">Tier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rankings as $row)
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50 {{ $row['user']->id === $user->id ? 'bg-primary/5' : '' }}">
                                    <td class="py-3 font-bold text-slate-800">#{{ $row['rank'] }}</td>
                                    <td class="py-3">
                                        <span class="font-bold text-slate-800 block">{{ $row['user']->first_name }} {{ $row['user']->last_name }}</span>
                                    </td>
                                    <td class="py-3 text-slate-600 font-semibold">{{ $row['activations'] }}</td>
                                    <td class="py-3">
                                        @if ($row['tier'])
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600">
                                                {{ $row['tier']->activation_target }}+ (₦{{ number_format($row['tier']->bonus_amount, 2) }})
                                            </span>
                                        @else
                                            <span class="text-slate-400">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        <div class="bg-slate-50 rounded-2xl w-14 h-14 flex items-center justify-center mb-3 mx-auto border border-slate-100">
                                            <i data-lucide="users" class="w-6 h-6 text-slate-400"></i>
                                        </div>
                                        <h6 class="font-bold text-slate-800 text-sm">No partners yet</h6>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endunless
    </div>
</x-app-layout>
