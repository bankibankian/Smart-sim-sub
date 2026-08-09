<x-app-layout>
    <title>SmartSIM - Request {{ $device['label'] }}</title>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Back Link -->
        <a href="{{ route('sims.' . $slug) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Back to {{ $device['label'] }}
        </a>

        <!-- Page Header -->
        <div class="flex items-center gap-4 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <x-sim-illustration :illustration="$device['illustration']" class="w-16 h-16 shrink-0" />
            <div>
                <h1 class="text-xl font-extrabold font-display text-slate-900">Request {{ $device['label'] }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">Select a network to choose from our uploaded number inventory.</p>
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

        <!-- Request Form Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <form action="{{ route('sims.request') }}" method="POST" class="space-y-5">
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
