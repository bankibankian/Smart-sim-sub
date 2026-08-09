<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Forgot Password</h1>
        <p class="text-sm text-slate-500 mt-1">Recover your account password securely.</p>
    </div>

    <div class="mb-6 p-4 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 text-sm flex gap-3 items-start">
        <i data-lucide="info" class="w-5 h-5 text-primary shrink-0 mt-0.5"></i>
        <span>
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </span>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email Address" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                    class="pl-10 pr-4" placeholder="name@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <x-primary-button type="submit" class="w-full font-display">
                <span>{{ __('Email Password Reset Link') }}</span>
                <i data-lucide="send" class="w-4 h-4"></i>
            </x-primary-button>
        </div>

        <div class="text-center text-sm text-slate-500 pt-4 border-t border-slate-100 font-display">
            Remembered your password? 
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-[#0049b8] transition">
                Log In
            </a>
        </div>
    </form>
</x-guest-layout>
