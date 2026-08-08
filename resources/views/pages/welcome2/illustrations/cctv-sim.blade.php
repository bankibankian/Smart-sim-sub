<svg viewBox="0 0 160 160" class="h-28 w-28" aria-hidden="true">
    <!-- viewing cone -->
    <path d="M118 78L150 40M118 88L150 128" style="stroke: var(--lp-border-strong);" stroke-width="1.3" stroke-dasharray="1 5" stroke-linecap="round" />
    <circle cx="140" cy="52" r="2.5" class="fill-primary" opacity="0.7" />
    <circle cx="144" cy="112" r="2.5" class="fill-primary" opacity="0.7" />

    <!-- wall bracket -->
    <path d="M22 44h16v10" fill="none" style="stroke: var(--lp-border-strong);" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    <line x1="38" y1="54" x2="50" y2="66" style="stroke: var(--lp-border-strong);" stroke-width="2" stroke-linecap="round" />

    <!-- camera body -->
    <g transform="rotate(-10 84 83)">
        <rect x="48" y="66" width="72" height="36" rx="14" style="fill: var(--lp-surface-alt); stroke: var(--lp-border-strong);" stroke-width="1.5" />
        <circle cx="112" cy="84" r="17" style="fill: var(--lp-surface); stroke: var(--lp-border-strong);" stroke-width="1.5" />
        <circle cx="112" cy="84" r="10" class="fill-primary/15 stroke-primary" stroke-width="1.3" />
        <circle cx="112" cy="84" r="4" class="fill-primary" />
        <circle cx="108" cy="80" r="1.6" fill="white" opacity="0.85" />
    </g>

    <!-- recording indicator -->
    <circle cx="60" cy="60" r="4" class="fill-vibrant">
        <animate attributeName="opacity" values="1;0.3;1" dur="1.6s" repeatCount="indefinite" />
    </circle>

    <!-- SIM badge -->
    <circle cx="50" cy="120" r="14" class="fill-vibrant" />
    <rect x="44" y="114" width="9" height="7" rx="1.5" fill="white" opacity="0.95" />
    <path d="M42 133a14 14 0 0116 0" class="stroke-vibrant" stroke-width="1.5" fill="none" stroke-linecap="round" opacity="0.5" />
</svg>
