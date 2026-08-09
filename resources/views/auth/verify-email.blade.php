<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Verify Email</h1>
        <p class="text-sm text-slate-500 mt-1">Please confirm your identity to get started.</p>
    </div>

    <div class="mb-6 p-4 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 text-sm flex gap-3 items-start">
        <i data-lucide="mail-open" class="w-5 h-5 text-primary shrink-0 mt-0.5"></i>
        <span>
            {{ __('Thanks for signing up! Please verify your email address by entering the 6-digit code we sent to') }}
            <strong class="text-slate-800">{{ auth()->user()->email }}</strong>.
        </span>
    </div>

    @if (session('status') == 'verification-otp-sent' || session('info'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm flex gap-3 items-start">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600 shrink-0 mt-0.5"></i>
            <span>
                {{ session('info') ?? __('A new OTP verification code has been sent to your email address.') }}
            </span>
        </div>
    @endif

    <!-- OTP Form -->
    <form method="POST" action="{{ route('verification.verify') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="code" value="{{ __('Verification Code') }}" />
            <x-text-input id="code" type="text" name="code" :value="old('code')" required autofocus
                   maxlength="6"
                   placeholder="••••••"
                   inputmode="numeric"
                   pattern="[0-9]*"
                   autocomplete="one-time-code"
                   class="text-center text-3xl tracking-[0.55em] py-3.5 font-mono" />
            
            @error('code')
                <p class="text-sm text-red-600 mt-2 flex gap-1.5 items-center font-medium">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <x-primary-button type="submit" class="w-full font-display">
            <span>{{ __('Verify Code') }}</span>
            <i data-lucide="shield-check" class="w-4 h-4"></i>
        </x-primary-button>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf

            <button type="submit" class="w-full flex items-center justify-center gap-1.5 text-sm font-semibold text-primary hover:text-[#0049b8] transition py-2 px-4 rounded-lg hover:bg-slate-50 transition-colors font-display">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span>{{ __('Resend Code') }}</span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
            @csrf

            <button type="submit" class="w-full text-center text-sm font-semibold text-slate-500 hover:text-slate-800 transition py-2 px-4 rounded-lg hover:bg-slate-50 transition-colors font-display">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
