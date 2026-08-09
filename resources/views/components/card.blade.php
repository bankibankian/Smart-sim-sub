@props(['padding' => 'p-6'])

{{-- Matches the dashboard's .dash-card edge treatment: slate-200 border, rounded-xl corners,
     primary-tinted border on hover. --}}
<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-sm transition-colors duration-200 hover:border-primary/30 ' . $padding]) }}>
    {{ $slot }}
</div>
