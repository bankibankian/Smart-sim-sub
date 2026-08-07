@php
    $tiers = [
        [
            'name' => 'Standard SIM',
            'price' => '1,500',
            'desc' => 'Standard retail SIM card configuration for daily public usage.',
            'featured' => false,
            'features' => [
                'Includes 10GB activation data',
                '₦2,000 airtime welcome bonus',
                'No registration contract',
                '24/7 client helpline access',
                'Self-service SIM swap active',
            ],
        ],
        [
            'name' => 'Premium SIM',
            'price' => '3,000',
            'desc' => 'Premium retail SIM card configuration with extra bonuses.',
            'featured' => true,
            'features' => [
                'Includes 25GB activation data',
                '₦5,000 airtime welcome bonus',
                'Premium VIP number pool',
                '24/7 client helpline access',
                'Free eSIM profiling optional',
            ],
        ],
        [
            'name' => 'Ultimate SIM',
            'price' => '7,500',
            'desc' => 'Ultimate retail SIM card with large data bundle and perks.',
            'featured' => false,
            'features' => [
                'Includes 100GB activation data',
                '₦15,000 airtime welcome bonus',
                'Gold tier number selection',
                'Priority routing network support',
                'Free intl. calls (60 mins)',
            ],
        ],
    ];
@endphp

<section id="pricing" class="mx-auto max-w-7xl px-6 py-20 lg:px-8 lg:py-28">
    <div class="max-w-2xl">
        <h2 class="text-3xl font-bold tracking-tight text-[var(--lp-text)] sm:text-4xl">Flexible rates built for everyone</h2>
        <p class="mt-4 text-lg leading-8 text-[var(--lp-text-soft)]">
            Choose the perfect plan for your connectivity needs — a high-value SIM pre-loaded with free
            data and airtime, one-time payment, no contracts.
        </p>
    </div>

    <div class="mt-12 grid gap-6 lg:grid-cols-3 lg:items-start">
        @foreach ($tiers as $tier)
            <div
                class="relative flex h-full flex-col rounded-xl border p-7"
                style="{{ $tier['featured'] ? 'border-color: var(--lp-border-strong); background: var(--lp-surface-strong); box-shadow: 0 16px 40px -20px rgba(0,86,210,0.35);' : 'border-color: var(--lp-border); background: var(--lp-surface);' }}"
            >
                @if ($tier['featured'])
                    <span class="absolute -top-3 left-7 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-white">
                        Most popular
                    </span>
                @endif

                <p class="text-sm font-semibold text-[var(--lp-text-soft)]">{{ $tier['name'] }}</p>
                <p class="mt-3 flex items-baseline gap-1">
                    <span class="text-lg font-semibold text-[var(--lp-text-soft)]">₦</span>
                    <span class="text-4xl font-extrabold text-[var(--lp-text)]">{{ $tier['price'] }}</span>
                    <span class="text-sm text-[var(--lp-text-faint)]">/one-time</span>
                </p>
                <p class="mt-3 text-sm text-[var(--lp-text-soft)]">{{ $tier['desc'] }}</p>

                <ul class="mt-6 space-y-3 text-sm text-[var(--lp-text)]">
                    @foreach ($tier['features'] as $feature)
                        <li class="flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 {{ $tier['featured'] ? 'text-primary' : 'text-vibrant' }}" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                <a
                    href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center justify-center rounded-lg px-5 py-2.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary {{ $tier['featured'] ? 'bg-primary text-white hover:bg-[#0049b8]' : 'border text-[var(--lp-text)] hover:bg-[var(--lp-surface-alt)]' }}"
                    @if (! $tier['featured']) style="border-color: var(--lp-border-strong);" @endif
                >
                    Get Started
                </a>
            </div>
        @endforeach
    </div>

    <p class="mt-10 text-sm text-[var(--lp-text-faint)]">
        Distributing in bulk? <a href="#support" class="font-semibold text-primary hover:underline">Talk to us about wholesale agent rates.</a>
    </p>
</section>
