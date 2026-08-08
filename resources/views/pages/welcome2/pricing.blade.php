@php
    $simTypes = [
        [
            'name' => 'POS SIM', 'price' => '1,000', 'desc' => 'For payment terminals',
            'illustration' => 'pages.welcome2.illustrations.pos-sim',
        ],
        [
            'name' => 'Camera SIM', 'price' => '1,500', 'desc' => 'For connected cameras',
            'illustration' => 'pages.welcome2.illustrations.cctv-sim',
        ],
        [
            'name' => 'CCTV SIM', 'price' => '2,000', 'desc' => 'For surveillance systems',
            'illustration' => 'pages.welcome2.illustrations.cctv-sim',
        ],
        [
            'name' => 'Router SIM', 'price' => '2,500', 'desc' => 'For mobile routers',
            'illustration' => 'pages.welcome2.illustrations.router-sim',
        ],
        [
            'name' => 'GPS SIM', 'price' => '3,000', 'desc' => 'For tracking devices',
            'illustration' => 'pages.welcome2.illustrations.gps-sim',
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
            <div class="flex flex-col items-center rounded-xl border p-5 text-center" style="border-color: var(--lp-border); background: var(--lp-surface);">
                @include($sim['illustration'])
                <p class="mt-2 text-sm font-semibold text-[var(--lp-text)]">{{ $sim['name'] }}</p>
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
