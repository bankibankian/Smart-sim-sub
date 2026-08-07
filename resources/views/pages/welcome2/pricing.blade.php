@php
    $simTypes = [
        [
            'name' => 'POS SIM', 'price' => '1,000', 'desc' => 'For payment terminals',
            'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
        ],
        [
            'name' => 'Camera SIM', 'price' => '1,500', 'desc' => 'For connected cameras',
            'icon' => 'M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316zM16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z',
        ],
        [
            'name' => 'CCTV SIM', 'price' => '2,000', 'desc' => 'For surveillance systems',
            'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
        ],
        [
            'name' => 'Router SIM', 'price' => '2,500', 'desc' => 'For mobile routers',
            'icon' => 'M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z',
        ],
        [
            'name' => 'GPS SIM', 'price' => '3,000', 'desc' => 'For tracking devices',
            'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
        ],
    ];
@endphp

<section id="pricing" class="mx-auto max-w-7xl px-6 py-20 lg:px-8 lg:py-28">
    <div class="max-w-2xl">
        <h2 class="text-3xl font-bold tracking-tight text-[var(--lp-text)] sm:text-4xl">SIM pricing, by device</h2>
        <p class="mt-4 text-lg leading-8 text-[var(--lp-text-soft)]">
            One-time activation fee, no contracts. Pick the SIM built for what you're connecting.
        </p>
    </div>

    <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($simTypes as $sim)
            <div class="rounded-xl border p-5" style="border-color: var(--lp-border); background: var(--lp-surface);">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sim['icon'] }}" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-semibold text-[var(--lp-text)]">{{ $sim['name'] }}</p>
                <p class="mt-1 text-xs text-[var(--lp-text-faint)]">{{ $sim['desc'] }}</p>
                <p class="mt-4 flex items-baseline gap-1">
                    <span class="text-sm font-semibold text-[var(--lp-text-soft)]">₦</span>
                    <span class="text-2xl font-bold text-[var(--lp-text)]">{{ $sim['price'] }}</span>
                </p>
            </div>
        @endforeach
    </div>

    <p class="mt-8 text-sm text-[var(--lp-text-faint)]">
        Buying in bulk? <a href="#support" class="font-semibold text-primary hover:underline">Ask about wholesale agent rates.</a>
    </p>
</section>
