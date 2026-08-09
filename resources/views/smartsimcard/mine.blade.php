<x-app-layout>
    <title>SmartSIM - My SIM</title>

    <div x-data="{ currentTab: 'sims' }" class="max-w-6xl mx-auto space-y-6">
        <!-- Page Header -->
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                </div>
                My SIM
            </h1>
            <p class="text-sm text-slate-500 mt-1">The SIM cards you own and the requests you've submitted.</p>
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

        <!-- Navigation Tabs -->
        <div class="bg-white rounded-lg border border-slate-100 shadow-sm p-2 flex gap-1">
            <button type="button" @click="currentTab = 'sims'" :class="currentTab === 'sims' ? 'bg-primary text-white' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                <i data-lucide="cpu" class="w-4 h-4"></i> My Registered SIMs
            </button>
            <button type="button" @click="currentTab = 'my_requests'" :class="currentTab === 'my_requests' ? 'bg-primary text-white' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                <i data-lucide="clock" class="w-4 h-4"></i> My Requests
            </button>
        </div>

        <!-- Tab: My Registered SIMs -->
        <div x-show="currentTab === 'sims'" class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 space-y-4">
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
                                    <span class="text-xs text-slate-400 block uppercase">{{ $sim->provider }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase
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
                                            <span class="block text-xs text-slate-400 capitalize">{{ $sim->user->role }}</span>
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
                                            })" class="bg-primary hover:bg-[#0049b8] text-white font-bold px-2 py-1 rounded-lg font-display text-xs tracking-wide">
                                                Delegate
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400 font-semibold">Delegated</span>
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
        <div x-show="currentTab === 'my_requests'" class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 space-y-4">
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
                                    <span class="text-xs text-slate-400 block uppercase">{{ $req->provider }}</span>
                                </td>
                                <td class="py-3 font-bold text-slate-700">₦{{ number_format($req->amount, 2) }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase
                                        {{ $req->status === 'approved' ? 'bg-emerald-50 text-emerald-600' : ($req->status === 'pending' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                                        {{ $req->status }}
                                    </span>
                                    @if ($req->admin_notes)
                                        <span class="block text-xs text-slate-400 italic mt-0.5">{{ $req->admin_notes }}</span>
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
</x-app-layout>
