<x-app-layout>
    <title>SmartSIM - SIM Inventory</title>

    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-display text-slate-800 flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <i data-lucide="database" class="w-5 h-5"></i>
                    </div>
                    SIM Inventory
                </h1>
                <p class="text-sm text-slate-500 mt-1">SIM numbers currently and previously assigned to you.</p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 text-emerald-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('warning'))
            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4 text-amber-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('warning') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-rose-50 border border-rose-100 rounded-lg p-4 text-rose-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Stock Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-{{ max(count($categories), 1) }} gap-4">
            @forelse ($categories as $cat)
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider truncate">{{ $cat['name'] }}</span>
                    <span class="text-xl font-extrabold text-slate-800 font-display mt-0.5 block">{{ $stockCounts[$cat['name']] ?? 0 }}</span>
                    <span class="text-[11px] text-slate-400">SIMs with you</span>
                </div>
            @empty
                <div class="col-span-full text-center text-slate-400 text-sm py-6">No SIM categories configured yet.</div>
            @endforelse
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <form action="{{ route('sims.inventory') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <x-select-input name="category" class="rounded-xl !text-xs">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat['name'] }}" {{ request('category') === $cat['name'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                    @endforeach
                </x-select-input>
                <x-select-input name="provider" class="rounded-xl !text-xs">
                    <option value="">All Networks</option>
                    @foreach ($providers as $prov)
                        <option value="{{ $prov }}" {{ request('provider') === $prov ? 'selected' : '' }}>{{ strtoupper($prov) }}</option>
                    @endforeach
                </x-select-input>
                <div class="flex gap-2">
                    <x-primary-button type="submit" class="flex-1 !text-xs">Filter</x-primary-button>
                    @if (request()->filled('category') || request()->filled('provider'))
                        <a href="{{ route('sims.inventory') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-white border border-slate-200 font-semibold text-xs text-slate-700 shadow-sm transition-all hover:bg-slate-50">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Your SIMs Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4"
             @if ($downlineUsers->count() > 0)
             x-data="{
                selectedSims: [],
                maxSelect: 50,
                toggleAll(checked) {
                    if (checked) {
                        let ids = Array.from(document.querySelectorAll('.sim-checkbox:not(:disabled)')).map(el => parseInt(el.value));
                        this.selectedSims = ids.slice(0, this.maxSelect);
                    } else {
                        this.selectedSims = [];
                    }
                },
                toggleSim(id, checked) {
                    if (checked) {
                        if (this.selectedSims.length >= this.maxSelect) {
                            Swal.fire('Limit Exceeded', 'You can only select up to 50 numbers at a time.', 'warning');
                            document.getElementById('sim_cb_' + id).checked = false;
                            return;
                        }
                        if (!this.selectedSims.includes(id)) {
                            this.selectedSims.push(id);
                        }
                    } else {
                        this.selectedSims = this.selectedSims.filter(x => x !== id);
                    }
                }
             }"
             @endif>
            <h3 class="font-bold text-slate-800 font-display pb-3 border-b border-slate-100">Your SIMs</h3>

            @if ($downlineUsers->count() > 0)
                <div x-show="selectedSims.length > 0"
                     style="display: none;"
                     class="flex items-center justify-between bg-indigo-50/80 border border-indigo-100 rounded-2xl p-3.5 animate-in slide-in-from-top duration-200">
                    <span class="text-xs font-semibold text-slate-700">
                        Selected: <span class="font-extrabold text-indigo-700" x-text="selectedSims.length"></span> / 50 SIMs
                    </span>
                    <button type="button" @click="Swal.fire({
                        title: 'Bulk Assign SIMs',
                        html: `
                            <div class='text-left space-y-2'>
                                <label class='text-xs font-bold text-slate-500 uppercase block'>Assign To</label>
                                <select id='bulk_user_id' class='w-full py-2.5 border rounded-xl font-medium text-slate-700'>
                                    <option value=''>Select Account</option>
                                    @foreach ($downlineUsers as $du)
                                        <option value='{{ $du->id }}'>{{ $du->first_name }} {{ $du->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonColor: '#0056D2',
                        confirmButtonText: 'Assign Selected',
                        preConfirm: () => {
                            const val = document.getElementById('bulk_user_id').value;
                            if (!val) {
                                Swal.showValidationMessage('Please select a user');
                            }
                            return val;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let f = document.createElement('form');
                            f.action = '{{ route('sims.bulk-assign') }}';
                            f.method = 'POST';
                            let inputs = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'user_id\' value=\'' + result.value + '\'>';
                            selectedSims.forEach(id => {
                                inputs += '<input type=\'hidden\' name=\'sim_ids[]\' value=\'' + id + '\'>';
                            });
                            f.innerHTML = inputs;
                            document.body.appendChild(f);
                            f.submit();
                        }
                    })" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 px-4 rounded-xl flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        Assign Checked
                    </button>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                            @if ($downlineUsers->count() > 0)
                                <th class="py-2.5 pl-1 w-8">
                                    <input type="checkbox" @change="toggleAll($event.target.checked)" class="rounded text-[#0056D2] focus:ring-[#0056D2]">
                                </th>
                            @endif
                            <th class="py-2.5">Number</th>
                            <th class="py-2.5">Category</th>
                            <th class="py-2.5">Network</th>
                            <th class="py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mySims as $sim)
                            @php
                                $eligible = match ($user->role) {
                                    'regional_manager' => $sim->status === 'ASSIGNED_TO_RM' && $sim->regional_manager_id === $user->id,
                                    'coordinator' => $sim->status === 'ASSIGNED_TO_COORDINATOR' && $sim->coordinator_id === $user->id,
                                    'partner' => $sim->status === 'ASSIGNED_TO_PARTNER' && $sim->partner_id === $user->id,
                                    default => false,
                                };
                            @endphp
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                @if ($downlineUsers->count() > 0)
                                    <td class="py-3 pl-1">
                                        @if ($eligible)
                                            <input type="checkbox" id="sim_cb_{{ $sim->id }}" value="{{ $sim->id }}"
                                                   :checked="selectedSims.includes({{ $sim->id }})"
                                                   @change="toggleSim({{ $sim->id }}, $event.target.checked)"
                                                   class="sim-checkbox rounded text-[#0056D2] focus:ring-[#0056D2]">
                                        @else
                                            <input type="checkbox" disabled class="rounded bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed">
                                        @endif
                                    </td>
                                @endif
                                <td class="py-3 font-bold text-slate-800">{{ $sim->number }}</td>
                                <td class="py-3 font-semibold text-slate-700">{{ $sim->category }}</td>
                                <td class="py-3 text-slate-500 uppercase">{{ $sim->provider }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border {{ \App\Support\SimStatus::badgeClasses($sim->status) }}">
                                        {{ \App\Support\SimStatus::label($sim->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $downlineUsers->count() > 0 ? 5 : 4 }}" class="py-8 text-center text-slate-400 font-semibold">You don't have any SIMs yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $mySims->links('vendor.pagination.custom') }}
        </div>

    </div>
</x-app-layout>
