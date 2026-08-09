<x-app-layout>
    <title>SmartSIM - SIM Inventory</title>

    <div x-data="{ openLookupModal: false, openResultModal: {{ session('check_result') ? 'true' : 'false' }} }" class="max-w-6xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-display text-slate-800 flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <i data-lucide="database" class="w-5 h-5"></i>
                    </div>
                    SIM Inventory
                </h1>
                <p class="text-sm text-slate-500 mt-1">Browse currently available SIM numbers before requesting one.</p>
            </div>
            <x-primary-button type="button" @click="openLookupModal = true" class="!text-xs !bg-slate-800 hover:!bg-slate-700">
                <i data-lucide="search" class="w-4 h-4"></i>
                SIM Owner Lookup
            </x-primary-button>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 text-emerald-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
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
                    <span class="text-[11px] text-slate-400">numbers in stock</span>
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

        <!-- Available Numbers Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-slate-800 font-display pb-3 border-b border-slate-100">Available Numbers</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                            <th class="py-2.5">Number</th>
                            <th class="py-2.5">Category</th>
                            <th class="py-2.5">Network</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($available as $sim)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                <td class="py-3 font-bold text-slate-800">{{ $sim->number }}</td>
                                <td class="py-3 font-semibold text-slate-700">{{ $sim->category }}</td>
                                <td class="py-3 text-slate-500 uppercase">{{ $sim->provider }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-400 font-semibold">No available numbers match this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $available->links('vendor.pagination.custom') }}
        </div>

        @if (session('check_result'))
            @php $res = session('check_result'); @endphp
            <!-- Check Result Modal -->
            <div x-show="openResultModal"
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display: none;">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openResultModal = false"></div>
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="relative w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-2xl p-6 overflow-hidden transform transition-all space-y-4">
                         <button type="button" @click="openResultModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                             <i data-lucide="x" class="w-5 h-5"></i>
                         </button>

                         <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                             <div class="w-9 h-9 rounded-xl {{ $res['success'] && isset($res['assigned']) && $res['assigned'] ? 'bg-primary/10 text-primary' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                                 <i data-lucide="search" class="w-4 h-4"></i>
                             </div>
                             <div>
                                 <h3 class="text-sm font-semibold text-slate-800 font-display">Check Number Result</h3>
                                 <p class="text-xs text-slate-400">Query for Number: <span class="font-bold text-slate-700">{{ request('number') }}</span></p>
                             </div>
                         </div>

                         @if (!$res['success'])
                             <p class="text-sm text-rose-600 font-semibold">{{ $res['message'] }}</p>
                         @else
                             <div class="space-y-3">
                                 <div class="flex justify-between items-center text-sm">
                                     <span class="text-slate-500 font-semibold">SIM Status:</span>
                                     <span class="font-bold uppercase px-2.5 py-0.5 rounded-full text-xs tracking-wider {{ $res['status'] === 'ACTIVATED' ? 'bg-emerald-50 text-emerald-600' : 'bg-primary/10 text-primary' }}">
                                         @if($res['status'] === 'UNASSIGNED')
                                             NOT ASSIGNED
                                         @else
                                             {{ str_replace('_', ' ', strtoupper($res['status'])) }}
                                         @endif
                                     </span>
                                 </div>
                                 <div class="flex justify-between items-center text-sm">
                                     <span class="text-slate-500 font-semibold">Category:</span>
                                     <span class="font-bold text-slate-700">{{ $res['category'] }}</span>
                                 </div>
                                 <div class="flex justify-between items-center text-sm">
                                     <span class="text-slate-500 font-semibold">Provider:</span>
                                     <span class="font-bold text-slate-700 uppercase">{{ $res['provider'] }}</span>
                                 </div>

                                 @if (isset($res['assigned']) && $res['assigned'])
                                     <div class="bg-primary/5 rounded-lg p-4 border border-primary/10 space-y-2 mt-2">
                                         <h5 class="text-xs font-bold text-primary uppercase tracking-wider">Assigned User Details</h5>
                                         <div class="flex justify-between text-sm">
                                             <span class="text-slate-500 font-semibold text-xs">Name:</span>
                                             <span class="font-bold text-slate-800">{{ $res['user_name'] }}</span>
                                         </div>
                                         <div class="flex justify-between text-sm">
                                             <span class="text-slate-500 font-semibold text-xs">Email:</span>
                                             <a href="mailto:{{ $res['user_email'] }}" class="font-bold text-primary hover:underline text-xs break-all">{{ $res['user_email'] }}</a>
                                         </div>
                                         <div class="flex justify-between text-sm">
                                             <span class="text-slate-500 font-semibold text-xs">Phone:</span>
                                             <a href="tel:{{ $res['user_phone'] }}" class="font-bold text-primary hover:underline text-xs">{{ $res['user_phone'] }}</a>
                                         </div>
                                     </div>
                                 @else
                                     <p class="text-sm text-emerald-600 font-semibold mt-2">This number is available and has not been assigned to any user.</p>
                                 @endif
                             </div>
                         @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- SIM Owner Lookup Modal -->
        <div x-show="openLookupModal"
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openLookupModal = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-2xl p-6 overflow-hidden transform transition-all">
                     <button type="button" @click="openLookupModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                         <i data-lucide="x" class="w-5 h-5"></i>
                     </button>
                     <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-4">
                         <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                             <i data-lucide="search" class="w-4 h-4"></i>
                         </div>
                         <div>
                             <h3 class="text-sm font-semibold text-slate-800 font-display">SIM Owner Lookup</h3>
                             <p class="text-xs text-slate-400">Search system numbers for assignee details.</p>
                         </div>
                     </div>
                     <form action="{{ route('sims.check') }}" method="GET" class="space-y-4">
                         <div class="space-y-1.5">
                             <x-input-label for="check_number" value="SIM Phone Number" />
                             <x-text-input type="tel" id="check_number" name="number" required
                                    class="rounded-xl text-center font-semibold" placeholder="e.g. 08031234567" />
                         </div>
                         <x-primary-button type="submit" class="w-full !text-xs !bg-slate-800 hover:!bg-slate-700">
                             <i data-lucide="search" class="w-3.5 h-3.5"></i>
                             Check SIM Owner
                         </x-primary-button>
                     </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
