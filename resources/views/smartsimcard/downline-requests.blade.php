<x-app-layout>
    <title>SmartSIM - Downline Requests</title>

    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex items-center gap-4 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center text-[#0056D2] shrink-0">
                <i data-lucide="inbox" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold font-display text-slate-900">Downline Requests</h1>
                <p class="text-sm text-slate-500 mt-0.5">SIM requests from users who named you as their upline — fulfill from your own held stock, or leave for our team to review.</p>
            </div>
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

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                            <th class="py-2.5">User</th>
                            <th class="py-2.5">Requested</th>
                            <th class="py-2.5">Category/Provider</th>
                            <th class="py-2.5">Your Stock</th>
                            <th class="py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $req)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="py-3">
                                    <span class="font-bold text-slate-700">{{ $req->user->first_name }} {{ $req->user->last_name }}</span>
                                    <span class="block text-xs text-slate-400 capitalize">{{ $req->user->role }}</span>
                                </td>
                                <td class="py-3 font-semibold text-slate-800">Qty: {{ $req->quantity }}</td>
                                <td class="py-3">
                                    <span class="font-medium text-slate-600 block">{{ $req->category }}</span>
                                    <span class="text-xs text-slate-400 block uppercase">{{ $req->provider }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $req->held_count >= $req->quantity ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                        {{ $req->held_count }} on hand
                                    </span>
                                </td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    @if ($req->held_count >= $req->quantity)
                                        <button type="button" @click="fetch('{{ route('sims.downline-requests.available-sims') }}?category={{ urlencode($req->category) }}&provider={{ urlencode($req->provider) }}')
                                            .then(r => r.json())
                                            .then(sims => {
                                                if (sims.length < {{ $req->quantity }}) {
                                                    Swal.fire('Not Enough Stock', 'Only ' + sims.length + ' SIM(s) available for {{ $req->category }} / {{ strtoupper($req->provider) }}, but {{ $req->quantity }} requested.', 'warning');
                                                    return;
                                                }
                                                let optionsHtml = sims.map(s => `
                                                    <label class='flex items-center gap-2 text-xs font-medium py-1'>
                                                        <input type='checkbox' class='fulfill-sim-cb' value='${s.id}'> ${s.number}
                                                    </label>
                                                `).join('');
                                                Swal.fire({
                                                    title: 'Select {{ $req->quantity }} SIM(s) to Hand Over',
                                                    html: `<div class='text-left max-h-64 overflow-y-auto space-y-1'>${optionsHtml}</div>`,
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#0056D2',
                                                    confirmButtonText: 'Fulfill Selected',
                                                    preConfirm: () => {
                                                        const checked = Array.from(document.querySelectorAll('.fulfill-sim-cb:checked')).map(el => el.value);
                                                        if (checked.length !== {{ $req->quantity }}) {
                                                            Swal.showValidationMessage('Please select exactly {{ $req->quantity }} SIM(s).');
                                                        }
                                                        return checked;
                                                    }
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        let f = document.createElement('form');
                                                        f.action = '{{ route('sims.downline-requests.fulfill', $req->id) }}';
                                                        f.method = 'POST';
                                                        let inputs = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'>';
                                                        result.value.forEach(id => { inputs += '<input type=\'hidden\' name=\'sim_ids[]\' value=\'' + id + '\'>'; });
                                                        f.innerHTML = inputs;
                                                        document.body.appendChild(f);
                                                        f.submit();
                                                    }
                                                })
                                            })" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                            Fulfill
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Not enough stock — our team will review</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 font-semibold">No pending requests from your downline.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
