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
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#93a0b2]">
                    <i data-lucide="mail" class="h-5 w-5" stroke-width="1.6"></i>
                </span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email"
                    class="block h-[42px] w-full rounded-lg border border-[#c8d2df] bg-[#eaf2ff] py-2 pl-10 pr-4 text-[14px] text-[#14243a] outline-none transition placeholder:text-[#8d99a9] focus:border-[#667c96] focus:bg-white focus:ring-2 focus:ring-[#1e3048]/10"
                >
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="mb-2 block text-[14px] font-medium text-[#24344a]">Password</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#93a0b2]">
                    <i data-lucide="lock" class="h-5 w-5" stroke-width="1.6"></i>
                </span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="block h-[42px] w-full rounded-lg border border-[#c8d2df] bg-[#eaf2ff] py-2 pl-10 pr-11 text-[14px] text-[#14243a] outline-none transition placeholder:text-[#8d99a9] focus:border-[#667c96] focus:bg-white focus:ring-2 focus:ring-[#1e3048]/10"
                >
                <button
                    type="button"
                    id="password-toggle"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#93a0b2] transition hover:text-[#526071]"
                    aria-label="Show password"
                >
                    <i data-lucide="eye" id="password-toggle-icon" class="h-5 w-5" stroke-width="1.6"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />

            <div class="mt-3 flex items-center justify-between">
                <label class="flex cursor-pointer items-center gap-2 text-[13px] text-[#526071]">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[#1e3048] focus:ring-[#1e3048]">
                    <span>Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[14px] font-medium text-[#1d2e45] hover:underline">
                        Forgot Password?
                    </a>
                @endif
            </div>
        </div>

        <button
            type="submit"
            class="mt-3 flex h-[42px] w-full items-center justify-center rounded-lg bg-[#1d2e45] px-5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#142238] focus:outline-none focus:ring-2 focus:ring-[#1d2e45]/30 focus:ring-offset-2 active:scale-[0.99]"
        >
            <span data-submit-text>Sign In</span>
        </button>

        @if (Route::has('register'))
            <p class="pt-2 text-center text-[14px] text-[#526071]">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-[#1d2e45] hover:underline">Sign up</a>
            </p>
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
