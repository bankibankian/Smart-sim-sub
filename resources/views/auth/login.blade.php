<x-guest-layout>
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <header class="mb-8 text-center">
        <h1 class="text-[26px] font-bold leading-tight tracking-[-0.025em] text-[#15253a]">Welcome Back</h1>
        <p class="mt-2 text-[15px] text-[#526071]">Sign in to your account</p>
    </header>

    <form method="POST" action="{{ route('login') }}" class="space-y-[18px]">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-[14px] font-medium text-[#24344a]">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200 text-sm"
                    placeholder="name@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <div class="flex justify-between items-center mb-2">
                <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-primary hover:text-[#0049b8] transition-colors font-display" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200 text-sm"
                    placeholder="••••••••" />
                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="eye" id="password-toggle-icon" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember"
                class="rounded border-slate-300 text-primary focus:ring-primary/20 focus:ring-offset-0 focus:outline-none w-4 h-4 cursor-pointer transition-all">
            <label for="remember_me" class="ms-2 text-sm text-slate-500 select-none cursor-pointer">{{ __('Remember me') }}</label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-primary hover:bg-[#0049b8] text-white font-semibold text-sm rounded-lg transition-colors duration-200 font-display">
                <span>{{ __('Log in') }}</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>

        @if (Route::has('register'))
            <div class="text-center text-sm text-slate-500 pt-4 border-t border-slate-100 font-display">
                Don't have an account? 
                <a href="{{ route('register') }}" class="font-semibold text-primary hover:text-[#0049b8] transition">
                    Register
                </a>
            </div>
        @endif
    </form>

    <script>
        document.getElementById('password-toggle')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-toggle-icon');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
            this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            lucide.createIcons();
        });
    </script>
</x-guest-layout>
