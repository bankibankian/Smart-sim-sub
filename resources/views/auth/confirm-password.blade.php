<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Confirm Password</h1>
        <p class="text-sm text-slate-500 mt-1">Please verify your credentials to continue.</p>
    </div>

    <div class="mb-6 p-4 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 text-sm flex gap-3 items-start">
        <i data-lucide="shield-alert" class="w-5 h-5 text-primary shrink-0 mt-0.5"></i>
        <span>
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </span>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password"
                    class="pl-10 pr-10" placeholder="••••••••" />
                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="eye" id="password-toggle-icon" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-primary hover:bg-[#0049b8] text-white font-semibold text-sm rounded-lg transition-colors duration-200 font-display">
                <span>{{ __('Confirm') }}</span>
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            </button>
        </div>
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
