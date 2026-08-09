<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Create an Account</h1>
        <p class="text-sm text-slate-500 mt-1">Sign up to access smart connectivity services.</p>
    </div>

    @if ($referrer)
        <div class="mb-6 p-4 rounded-lg bg-primary/5 border border-primary/10 text-slate-700 text-sm flex gap-3 items-start">
            <i data-lucide="user-plus" class="w-5 h-5 text-primary shrink-0 mt-0.5"></i>
            <span>
                You were invited by <strong class="text-slate-900">{{ $referrer->first_name ?? $referrer->email }}</strong>. Sign up to join their network.
            </span>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="ref" value="{{ old('ref', request('ref')) }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email Address" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username"
                    class="pl-10 pr-4" placeholder="name@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password" />
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

            <!-- Password Strength Progress -->
            <div class="mt-2">
                <div class="flex justify-between items-center mb-1">
                    <span id="strength-text" class="text-xs font-semibold"></span>
                </div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                    <div id="strength-bar" class="h-full bg-red-500 transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Confirm Password" />
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
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <!-- Terms & Conditions -->
        <div class="flex items-start">
            <input type="checkbox" name="terms" id="terms" value="1"
                class="rounded border-slate-300 text-primary focus:ring-primary/20 focus:ring-offset-0 focus:outline-none w-4 h-4 cursor-pointer transition-all mt-1 flex-shrink-0"
                {{ old('terms') ? 'checked' : '' }}>
            <label for="terms" class="ms-2 text-sm text-slate-500 select-none cursor-pointer">
                I agree to the
                <a href="#" class="font-semibold text-primary hover:text-[#0049b8] transition text-decoration-none font-display">Terms &amp; Conditions</a>
                and
                <a href="#" class="font-semibold text-primary hover:text-[#0049b8] transition text-decoration-none font-display">Privacy Policy</a>
            </label>
        </div>
        <x-input-error :messages="$errors->get('terms')" class="mt-1.5" />

        <!-- Submit Button -->
        <div class="pt-2">
            <x-primary-button type="submit" class="w-full font-display">
                <span>{{ __('Register') }}</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </x-primary-button>
        </div>

        <!-- Login Link -->
        <div class="text-center text-sm text-slate-500 pt-4 border-t border-slate-100 font-display">
            Already registered? 
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-[#0049b8] transition">
                Log In
            </a>
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

        // Live Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthText = document.getElementById('strength-text');
        const strengthBar = document.getElementById('strength-bar');

        passwordInput.addEventListener('input', function () {
            const value = this.value;

            // --- Empty: hide the indicator entirely ---
            if (value.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'h-full transition-all duration-300';
                strengthText.textContent = '';
                strengthText.className = 'text-xs font-semibold';
                return;
            }

            // --- Under 8 characters: always "Too short" ---
            if (value.length < 8) {
                const partial = (value.length / 8) * 20; // tiny partial fill up to 20%
                strengthBar.style.width = partial + '%';
                strengthBar.className = 'h-full bg-red-500 transition-all duration-300';
                strengthText.textContent = 'Too short — minimum 8 characters';
                strengthText.className = 'text-xs font-semibold text-red-500';
                return;
            }

            // --- 8+ characters: score on complexity ---
            let score = 1; // length >= 8 already guaranteed, start at 1
            if (/[A-Z]/.test(value)) score++;
            if (/[a-z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            let width    = (score / 5) * 100;
            let barClass = 'bg-red-500';
            let label    = 'Weak';
            let textClass = 'text-red-500';

            if (score === 2) {
                barClass  = 'bg-amber-500';
                label     = 'Fair';
                textClass = 'text-amber-500';
            } else if (score === 3) {
                barClass  = 'bg-cyan-500';
                label     = 'Good';
                textClass = 'text-cyan-500';
            } else if (score === 4) {
                barClass  = 'bg-blue-500';
                label     = 'Strong';
                textClass = 'text-blue-500';
            } else if (score === 5) {
                barClass  = 'bg-green-500';
                label     = 'Very Strong ✓';
                textClass = 'text-green-500';
            }

            strengthBar.style.width = width + '%';
            strengthBar.className = 'h-full transition-all duration-300 ' + barClass;
            strengthText.textContent = label;
            strengthText.className = 'text-xs font-semibold ' + textClass;
        });
    </script>
</x-guest-layout>
