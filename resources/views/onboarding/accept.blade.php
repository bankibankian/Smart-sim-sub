<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Set Your Password</h1>
        <p class="text-sm text-slate-500 mt-1">
            Welcome, {{ $invitee->first_name }}. Set a password to activate your
            <strong class="text-slate-900">{{ str_replace('_', ' ', $invitee->role) }}</strong> account.
        </p>
    </div>

    <form method="POST" action="{{ url()->full() }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" class="mt-1" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <x-primary-button type="submit" class="w-full font-display justify-center">
                <span>Activate My Account</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
