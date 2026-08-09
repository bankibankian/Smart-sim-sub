<x-app-layout>
    <title>SmartSIM - {{ $device['label'] }}</title>

    <div x-data="{ openRequestSimModal: false }" class="max-w-5xl mx-auto space-y-6">
        <!-- Back Link -->
        <a href="{{ route('sims.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Back to SIM Services
        </a>

        <!-- Device Header Card -->
        <div class="flex flex-col sm:flex-row items-center gap-6 bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <x-sim-illustration :illustration="$device['illustration']" class="w-24 h-24 sm:w-28 sm:h-28 shrink-0" />
            <div class="flex-1 text-center sm:text-left">
                <h1 class="text-2xl font-extrabold font-display text-slate-900">{{ $device['label'] }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $device['desc'] }}</p>
            </div>
            <div class="shrink-0 text-center sm:text-right">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Activation Fee</span>
                <span class="text-2xl font-extrabold text-primary font-display">₦{{ number_format($price ?? 0, 2) }}</span>
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left: Activation Request -->
            <div class="lg:col-span-6">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 font-display">Activate a {{ $device['label'] }}</h3>
                            <p class="text-xs text-slate-400">Request activation for a {{ $device['label'] }} assigned to your account.</p>
                        </div>
                    </div>

                    <form action="{{ route('sims.activate') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <x-input-label for="activate_sim_id" value="Select SIM Number" />
                            <x-select-input id="activate_sim_id" name="sim_id" required class="rounded-xl font-medium">
                                <option value="">Select SIM</option>
                                @forelse ($sims as $sim)
                                    <option value="{{ $sim->id }}">{{ $sim->number }} ({{ strtoupper($sim->provider) }})</option>
                                @empty
                                    <option value="" disabled>No inactive {{ $device['label'] }}s found.</option>
                                @endforelse
                            </x-select-input>
                        </div>

                        <div class="bg-primary/5 border border-primary/10 rounded-lg p-4 space-y-1 shadow-inner">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Activation Fee</span>
                                <span class="text-base font-extrabold text-primary font-display">₦{{ number_format($price ?? 0, 2) }}</span>
                            </div>
                            <p class="text-xs text-slate-400">This amount will be debited from your wallet balance.</p>
                        </div>

                        <x-primary-button type="submit" class="w-full !text-xs !bg-emerald-600 hover:!bg-emerald-700">
                            <i data-lucide="power" class="w-3.5 h-3.5"></i>
                            Request Activation
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Right: Request a new number -->
            <div class="lg:col-span-6">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex flex-col items-start gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 font-display">Don't have a {{ $device['label'] }} yet?</h3>
                            <p class="text-xs text-slate-400">Request one from our uploaded number inventory.</p>
                        </div>
                    </div>
                    <x-primary-button type="button" @click="openRequestSimModal = true" class="!text-xs">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        Request {{ $device['label'] }}
                    </x-primary-button>
                </div>
            </div>
        </div>

        <!-- Request SIM Card Modal (category locked) -->
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
                <div class="relative w-full max-w-md bg-white rounded-xl border border-slate-100 shadow-2xl p-6 overflow-hidden transform transition-all">
                     <button type="button" @click="openRequestSimModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                         <i data-lucide="x" class="w-5 h-5"></i>
                     </button>
                     <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-4">
                         <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                             <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                         </div>
                         <div>
                             <h3 class="font-bold text-slate-800 font-display">Request {{ $device['label'] }}</h3>
                             <p class="text-xs text-slate-400">Select a network to choose from uploaded numbers.</p>
                         </div>
                     </div>
                     <form action="{{ route('sims.request') }}" method="POST" class="space-y-4">
                         @csrf
                         <input type="hidden" name="category" value="{{ $category }}">
                         <div class="space-y-1.5">
                             <x-input-label value="SIM Category" />
                             <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700">
                                 {{ $device['label'] }}
                             </div>
                         </div>
                         <div class="space-y-1.5">
                             <x-input-label for="req_provider" value="Network Operator" />
                             <x-select-input id="req_provider" name="provider" required class="rounded-xl font-medium">
                                 <option value="">Select Network</option>
                                 @foreach ($providers as $prov)
                                     <option value="{{ $prov }}">{{ strtoupper($prov) }}</option>
                                 @endforeach
                             </x-select-input>
                         </div>
                         <div class="space-y-1.5">
                             <x-input-label for="req_number" value="Available Numbers" />
                             <x-select-input id="req_number" name="sim_id" required disabled class="rounded-xl font-medium">
                                 <option value="">Select Number (Select Network First)</option>
                             </x-select-input>
                         </div>
                         <x-primary-button type="submit" class="w-full">
                             <i data-lucide="send" class="w-4 h-4"></i>
                             Submit Request
                         </x-primary-button>
                     </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function () {
        const category = @json($category);
        const providerSelect = $('#req_provider');
        const numberSelect = $('#req_number');

        function fetchNumbers() {
            const provider = providerSelect.val();

            if (!provider) {
                numberSelect.empty().append('<option value="">Select Number (Select Network First)</option>').prop('disabled', true);
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

        providerSelect.on('change', fetchNumbers);
    });
    </script>
    @endpush
</x-app-layout>
