<x-app-layout>
    <title>SmartSIM - SIM Services</title>

    <div x-data="{ openRequestSimModal: false, openActivateSimModal: false, openLookupModal: false, openResultModal: {{ session('check_result') ? 'true' : 'false' }} }" class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold font-display text-slate-900 flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-[#0056D2] border border-indigo-100/50 shadow-sm">
                        <i data-lucide="cpu" class="w-5 h-5"></i>
                    </div>
                    SIM Services
                </h1>
                <p class="text-sm text-slate-500 mt-1">Manage, activate, and delegate corporate & specialized SIM cards.</p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('warning'))
            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-amber-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('warning') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

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
                    <div class="relative w-full max-w-md bg-white rounded-3xl border border-slate-100 shadow-2xl p-6 overflow-hidden transform transition-all space-y-4">
                         <button type="button" @click="openResultModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                             <i data-lucide="x" class="w-5 h-5"></i>
                         </button>
                         
                         <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                             <div class="w-9 h-9 rounded-xl {{ $res['success'] && isset($res['assigned']) && $res['assigned'] ? 'bg-indigo-50 text-[#0056D2]' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                                 <i data-lucide="search" class="w-4 h-4"></i>
                             </div>
                             <div>
                                 <h3 class="font-bold text-slate-800 font-display">Check Number Result</h3>
                                 <p class="text-xs text-slate-400">Query for Number: <span class="font-bold text-slate-650">{{ request('number') }}</span></p>
                             </div>
                         </div>

                         @if (!$res['success'])
                             <p class="text-sm text-rose-600 font-semibold">{{ $res['message'] }}</p>
                         @else
                             <div class="space-y-3">
                                 <div class="flex justify-between items-center text-sm">
                                     <span class="text-slate-450 font-semibold">SIM Status:</span>
                                     <span class="font-bold uppercase px-2.5 py-0.5 rounded-full text-[10px] tracking-wider {{ $res['status'] === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-[#0056D2]' }}">
                                         @if($res['status'] === 'active')
                                             ACTIVATED
                                         @elseif($res['status'] === 'available')
                                             NOT ASSIGNED
                                         @else
                                             {{ strtoupper($res['status']) }}
                                         @endif
                                     </span>
                                 </div>
                                 <div class="flex justify-between items-center text-sm">
                                     <span class="text-slate-450 font-semibold">Category:</span>
                                     <span class="font-bold text-slate-700">{{ $res['category'] }}</span>
                                 </div>
                                 <div class="flex justify-between items-center text-sm">
                                     <span class="text-slate-450 font-semibold">Provider:</span>
                                     <span class="font-bold text-slate-700 uppercase">{{ $res['provider'] }}</span>
                                 </div>

                                 @if (isset($res['assigned']) && $res['assigned'])
                                     <div class="bg-indigo-50/50 rounded-2xl p-4 border border-indigo-100/50 space-y-2 mt-2">
                                         <h5 class="text-xs font-bold text-[#0056D2] uppercase tracking-wider">Assigned User Details</h5>
                                         <div class="flex justify-between text-sm">
                                             <span class="text-slate-500 font-semibold text-xs">Name:</span>
                                             <span class="font-bold text-slate-800">{{ $res['user_name'] }}</span>
                                         </div>
                                         <div class="flex justify-between text-sm">
                                             <span class="text-slate-500 font-semibold text-xs">Email:</span>
                                             <a href="mailto:{{ $res['user_email'] }}" class="font-bold text-[#0056D2] hover:underline text-xs break-all">{{ $res['user_email'] }}</a>
                                         </div>
                                         <div class="flex justify-between text-sm">
                                             <span class="text-slate-500 font-semibold text-xs">Phone:</span>
                                             <a href="tel:{{ $res['user_phone'] }}" class="font-bold text-[#0056D2] hover:underline text-xs">{{ $res['user_phone'] }}</a>
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

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Panel: Actions & Forms (Forms Column) -->
            <div class="lg:col-span-5 flex flex-col gap-6">
                <!-- Activation Request Form Card -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center text-[#0056D2]">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 font-display">SIM Activation Request</h3>
                            <p class="text-xs text-slate-400">Request activation for SIMs assigned to your account.</p>
                        </div>
                    </div>

                    @php
                        $categoryPrices = [];
                        foreach ($categories as $cat) {
                            $categoryPrices[$cat['name']] = $cat['price'];
                        }
                    @endphp
                    <form action="{{ route('sims.activate') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <label for="activate_sim_id" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Select SIM Number</label>
                            <select id="activate_sim_id" name="sim_id" required class="w-full py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0056D2]/20 focus:border-[#0056D2] text-slate-700 font-medium">
                                <option value="">Select SIM</option>
                                @forelse ($sims as $sim)
                                    @if ($sim->status !== 'active')
                                        <option value="{{ $sim->id }}" data-price="{{ $categoryPrices[$sim->category] ?? 0.00 }}">{{ $sim->number }} - {{ $sim->category }} ({{ strtoupper($sim->provider) }})</option>
                                    @endif
                                @empty
                                    <option value="" disabled>No inactive SIM cards found.</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- Dynamic Price indicator for Activation Request -->
                        <div id="activation-price-box" class="hidden bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 space-y-1 shadow-inner">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Activation Fee</span>
                                <span id="activation-price-value" class="text-base font-extrabold text-indigo-700 font-display">₦0.00</span>
                            </div>
                            <p class="text-[10px] text-slate-400">This amount will be debited from your wallet balance.</p>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all duration-200 flex items-center justify-center gap-2">
                            <i data-lucide="power" class="w-3.5 h-3.5"></i>
                            Request Activation
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Panel: Data Lists & Tabbed Tables -->
            <div class="lg:col-span-7 flex flex-col gap-6" x-data="{ currentTab: 'sims' }">
                <!-- Action Row -->
                <div class="flex flex-wrap items-center gap-3 bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
                    <button type="button" @click="openRequestSimModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#354062] hover:to-[#465784] text-white font-bold text-xs rounded-xl shadow-sm transition-all duration-150">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        Request SIM Card
                    </button>

                    <button type="button" @click="openLookupModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all duration-150">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        SIM Owner Lookup
                    </button>
                </div>

                <!-- Modals -->
                <!-- 1. Request SIM Card Modal -->
                <div x-show="openRequestSimModal" 
                     class="fixed inset-0 z-50 overflow-y-auto" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     style="display: none;">
                    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openRequestSimModal = false"></div>
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <div class="relative w-full max-w-md bg-white rounded-3xl border border-slate-100 shadow-2xl p-6 overflow-hidden transform transition-all">
                             <button type="button" @click="openRequestSimModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                                 <i data-lucide="x" class="w-5 h-5"></i>
                             </button>
                             <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-4">
                                 <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center text-[#0056D2]">
                                     <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                 </div>
                                 <div>
                                     <h3 class="font-bold text-slate-800 font-display">Request SIM Card</h3>
                                     <p class="text-xs text-slate-400">Select network and category to choose from uploaded numbers.</p>
                                 </div>
                             </div>
                             <form action="{{ route('sims.request') }}" method="POST" class="space-y-4">
                                 @csrf
                                 <div class="space-y-1.5">
                                     <label for="req_category" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">SIM Category</label>
                                     <select id="req_category" name="category" required class="w-full py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0056D2]/20 focus:border-[#0056D2] text-slate-700 font-medium">
                                         <option value="">Select Category</option>
                                         @foreach ($categories as $cat)
                                             <option value="{{ $cat['name'] }}">{{ $cat['name'] }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="space-y-1.5">
                                     <label for="req_provider" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Network Operator</label>
                                     <select id="req_provider" name="provider" required class="w-full py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0056D2]/20 focus:border-[#0056D2] text-slate-700 font-medium">
                                         <option value="">Select Network</option>
                                         @foreach ($providers as $prov)
                                             <option value="{{ $prov }}">{{ strtoupper($prov) }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="space-y-1.5">
                                     <label for="req_number" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Available Numbers</label>
                                     <select id="req_number" name="sim_id" required class="w-full py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0056D2]/20 focus:border-[#0056D2] text-slate-700 font-medium" disabled>
                                         <option value="">Select Number (Select Category & Network First)</option>
                                     </select>
                                 </div>
                                 <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#354062] hover:to-[#465784] text-white font-bold text-sm rounded-xl shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                                     <i data-lucide="send" class="w-4 h-4"></i>
                                     Submit Request
                                 </button>
                             </form>
                        </div>
                    </div>
                </div>

                <!-- 2. SIM Owner Lookup Modal -->
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
                        <div class="relative w-full max-w-md bg-white rounded-3xl border border-slate-100 shadow-2xl p-6 overflow-hidden transform transition-all">
                             <button type="button" @click="openLookupModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                                 <i data-lucide="x" class="w-5 h-5"></i>
                             </button>
                             <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-4">
                                 <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center text-[#0056D2]">
                                     <i data-lucide="search" class="w-4 h-4"></i>
                                 </div>
                                 <div>
                                     <h3 class="font-bold text-slate-800 font-display">SIM Owner Lookup</h3>
                                     <p class="text-xs text-slate-400">Search system numbers for assignee details.</p>
                                 </div>
                             </div>
                             <form action="{{ route('sims.check') }}" method="GET" class="space-y-4">
                                 <div class="space-y-1.5">
                                     <label for="check_number" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">SIM Phone Number</label>
                                     <input type="tel" id="check_number" name="number" required placeholder="e.g. 08031234567"
                                            class="w-full text-center py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0056D2]/20 focus:border-[#0056D2] transition-all text-slate-800 font-semibold">
                                 </div>
                                 <button type="submit" class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow transition-all duration-200 flex items-center justify-center gap-2">
                                     <i data-lucide="search" class="w-3.5 h-3.5"></i>
                                     Check SIM Owner
                                 </button>
                             </form>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-2 flex gap-1">
                    <button type="button" @click="currentTab = 'sims'" :class="currentTab === 'sims' ? 'bg-[#0056D2] text-white' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                        <i data-lucide="cpu" class="w-4 h-4"></i> My SIM Cards
                    </button>
                    <button type="button" @click="currentTab = 'my_requests'" :class="currentTab === 'my_requests' ? 'bg-[#0056D2] text-white' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                        <i data-lucide="clock" class="w-4 h-4"></i> My Requests
                    </button>
                </div>

                <!-- Tab: SIM Inventory / My SIM Cards -->
                <div x-show="currentTab === 'sims'" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h3 class="font-bold text-slate-800 font-display pb-3 border-b border-slate-100">
                        My Registered SIMs
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                                    <th class="py-2.5">Number</th>
                                    <th class="py-2.5">Category/Network</th>
                                    <th class="py-2.5">Status</th>
                                    @if ($user->role === 'partner')
                                        <th class="py-2.5">Assignee</th>
                                        <th class="py-2.5 text-right">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sims as $sim)
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                        <td class="py-3 font-bold text-slate-800">{{ $sim->number }}</td>
                                        <td class="py-3">
                                            <span class="font-semibold text-slate-700 block">{{ $sim->category }}</span>
                                            <span class="text-[10px] text-slate-400 block uppercase">{{ $sim->provider }}</span>
                                        </td>
                                        <td class="py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase 
                                                {{ $sim->status === 'active' ? 'bg-emerald-50 text-emerald-600' : ($sim->status === 'assigned' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-600') }}">
                                                @if($sim->status === 'active')
                                                    ACTIVATED
                                                @elseif($sim->status === 'available')
                                                    NOT ASSIGNED
                                                @else
                                                    {{ $sim->status }}
                                                @endif
                                            </span>
                                        </td>
                                        @if ($user->role === 'partner')
                                            <td class="py-3">
                                                @if ($sim->user_id !== $sim->partner_id && $sim->user)
                                                    <span class="font-semibold text-slate-700">{{ $sim->user->first_name }} {{ $sim->user->last_name }}</span>
                                                    <span class="block text-[10px] text-slate-400 capitalize">{{ $sim->user->role }}</span>
                                                @else
                                                    <span class="text-slate-400 font-semibold italic">Owned by You</span>
                                                @endif
                                            </td>
                                            <td class="py-3 text-right">
                                                @if ($sim->user_id === $user->id)
                                                    <button type="button" @click="Swal.fire({
                                                        title: 'Assign SIM Card',
                                                        html: `
                                                            <div class='text-left space-y-2'>
                                                                <label class='text-xs font-bold text-slate-500 uppercase block'>Choose Business or Agent</label>
                                                                <select id='partner_user_id' class='w-full py-2.5 border rounded-xl font-medium text-slate-700'>
                                                                    <option value=''>Select Account</option>
                                                                    @foreach ($assignableUsers as $au)
                                                                        <option value='{{ $au->id }}'>{{ $au->first_name }} {{ $au->last_name }} ({{ $au->role }})</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        `,
                                                        showCancelButton: true,
                                                        confirmButtonColor: '#0056D2',
                                                        confirmButtonText: 'Assign Now',
                                                        preConfirm: () => {
                                                            const val = document.getElementById('partner_user_id').value;
                                                            if (!val) {
                                                                Swal.showValidationMessage('Please select a user');
                                                            }
                                                            return val;
                                                        }
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            let f = document.createElement('form');
                                                            f.action = '{{ route('partner.sims.assign') }}';
                                                            f.method = 'POST';
                                                            f.innerHTML = `@csrf<input type='hidden' name='sim_id' value='{{ $sim->id }}'><input type='hidden' name='user_id' value='${result.value}'>`;
                                                            document.body.appendChild(f);
                                                            f.submit();
                                                        }
                                                    })" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-2 py-1 rounded-lg font-display text-[10px] tracking-wide">
                                                        Delegate
                                                    </button>
                                                @else
                                                    <span class="text-[10px] text-slate-400 font-semibold">Delegated</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 font-semibold">No SIM records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $sims->withQueryString()->links('vendor.pagination.custom') }}
                </div>

                <!-- Tab: My Requests -->
                <div x-show="currentTab === 'my_requests'" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h3 class="font-bold text-slate-800 font-display pb-3 border-b border-slate-100">My SIM Requests</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                                    <th class="py-2.5">Number</th>
                                    <th class="py-2.5">Request Type</th>
                                    <th class="py-2.5">Category/Network</th>
                                    <th class="py-2.5">Amount</th>
                                    <th class="py-2.5">Status</th>
                                    <th class="py-2.5">Date Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $req)
                                    <tr class="border-b border-slate-50">
                                        <td class="py-3 font-bold text-slate-800">{{ $req->number }}</td>
                                        <td class="py-3 capitalize font-semibold text-slate-700">{{ $req->request_type }}</td>
                                        <td class="py-3">
                                            <span class="font-semibold text-slate-600 block">{{ $req->category }}</span>
                                            <span class="text-[10px] text-slate-400 block uppercase">{{ $req->provider }}</span>
                                        </td>
                                        <td class="py-3 font-bold text-slate-700">₦{{ number_format($req->amount, 2) }}</td>
                                        <td class="py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $req->status === 'approved' ? 'bg-emerald-50 text-emerald-600' : ($req->status === 'pending' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                                                {{ $req->status }}
                                            </span>
                                            @if ($req->admin_notes)
                                                <span class="block text-[9px] text-slate-400 italic mt-0.5">{{ $req->admin_notes }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-slate-400 font-medium">{{ $req->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 font-semibold">You haven't submitted any requests yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $requests->withQueryString()->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function () {
        const categorySelect = $('#req_category');
        const providerSelect = $('#req_provider');
        const numberSelect = $('#req_number');

        function fetchNumbers() {
            const category = categorySelect.val();
            const provider = providerSelect.val();

            if (!category || !provider) {
                numberSelect.empty().append('<option value="">Select Number (Select Category & Network First)</option>').prop('disabled', true);
                return;
            }

            numberSelect.empty().append('<option value="">Loading numbers...</option>').prop('disabled', true);

            $.ajax({
                type: 'GET',
                url: '{{ route('sims.available') }}',
                data: { category: category, provider: provider },
                dataType: 'json',
                success: function (response) {
                    numberSelect.empty();
                    if (response.length === 0) {
                        numberSelect.append('<option value="">No available numbers found</option>').prop('disabled', true);
                    } else {
                        numberSelect.append('<option value="">Select Number</option>');
                        response.forEach(function (sim) {
                            numberSelect.append('<option value="' + sim.id + '">' + sim.number + '</option>');
                        });
                        numberSelect.prop('disabled', false);
                    }
                },
                error: function () {
                    numberSelect.empty().append('<option value="">Error loading numbers. Try again.</option>').prop('disabled', true);
                }
            });
        }

        categorySelect.on('change', function() {
            fetchNumbers();
        });
        
        providerSelect.on('change', fetchNumbers);

        // Activation Request pricing dynamic display
        const activateSimSelect = $('#activate_sim_id');
        activateSimSelect.on('change', function() {
            const selectedOpt = $(this).find('option:selected');
            const price = selectedOpt.data('price');
            if (price !== undefined && price !== '') {
                $('#activation-price-value').text('₦' + parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#activation-price-box').removeClass('hidden');
            } else {
                $('#activation-price-box').addClass('hidden');
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
