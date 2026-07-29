<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Welcome Back User</h1>
        <p class="text-sm text-slate-500 mt-1">Sign in to your accounts.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#42517c] focus:ring-2 focus:ring-[#42517c]/20 transition-all duration-200 text-sm shadow-sm"
                    placeholder="name@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-[#42517c] hover:text-[#55699e] transition-colors font-display" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#42517c] focus:ring-2 focus:ring-[#42517c]/20 transition-all duration-200 text-sm shadow-sm"
                    placeholder="••••••••" />
                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="eye" id="password-toggle-icon" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember"
                class="rounded border-slate-300 text-[#42517c] focus:ring-[#42517c]/20 focus:ring-offset-0 focus:outline-none w-4 h-4 cursor-pointer transition-all">
            <label for="remember_me" class="ms-2 text-sm text-slate-500 select-none cursor-pointer">{{ __('Remember me') }}</label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-[#42517c] to-[#55699e] hover:from-[#354268] hover:to-[#42517c] text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-950/10 hover:shadow-indigo-950/20 active:scale-[0.98] transition-all duration-200 font-display">
                <span>{{ __('Log in') }}</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Registration Link -->
        @if (Route::has('register'))
            <div class="text-center text-sm text-slate-500 pt-4 border-t border-slate-100 font-display">
                Don't have an account? 
                <a href="{{ route('register') }}" class="font-semibold text-[#42517c] hover:text-[#55699e] transition">
                    Register
                </a>
            </div>
        @endif
    </form>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                toggleIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</x-guest-layout>
