@props(['illustration'])

{{-- The shared SIM illustrations use --lp-* CSS variables scoped to the landing page's theme.
     Outside that scope (e.g. the authenticated dashboard) we supply the light-mode values inline
     so the artwork renders correctly without pulling in the landing page's dark-mode system. --}}
<div {{ $attributes->merge(['class' => 'flex items-center justify-center']) }}
     style="--lp-surface:#FFFFFF; --lp-surface-alt:#F1F5F9; --lp-border:#DDE3EA; --lp-border-strong:#C4CEDB;">
    @include($illustration)
</div>
