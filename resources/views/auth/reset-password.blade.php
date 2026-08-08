<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Reset Password</h1>
        <p class="text-sm text-slate-500 mt-1">Choose a strong password to secure your account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email Address" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username"
                    class="pl-10 pr-4" placeholder="name@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="New Password" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <x-text-input id="password" type="password" name="password" required autocomplete="new-password"
                    class="pl-10 pr-10" placeholder="••••••••" />
                <button type="button" onclick="togglePasswordVisibility('password', 'password-toggle-icon')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="eye" id="password-toggle-icon" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Confirm New Password" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="pl-10 pr-10" placeholder="••••••••" />
                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'confirm-password-toggle-icon')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="eye" id="confirm-password-toggle-icon" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <div class="pt-3">
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-primary hover:bg-[#0049b8] text-white font-semibold text-sm rounded-lg transition-colors duration-200 font-display">
                <span>{{ __('Reset Password') }}</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
    </form>

    <script>
        function togglePasswordVisibility(fieldId, iconId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);
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
