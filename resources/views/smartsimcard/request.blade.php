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
                <p class="text-sm text-slate-500 mt-0.5">Tell us how many you need — the admin will assign the specific number(s).</p>
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
                    <x-input-label for="req_quantity" value="Quantity" />
                    <x-text-input type="number" id="req_quantity" name="quantity" min="1" max="20" value="1" required class="rounded-xl font-medium" />
                    <p class="text-xs text-slate-400">The admin will review your request and assign specific SIM number(s) to you.</p>
                </div>

                <x-primary-button type="submit" class="w-full">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Submit Request
                </x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
