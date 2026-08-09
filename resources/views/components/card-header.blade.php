@props(['icon' => null, 'title', 'subtitle' => null, 'border' => true])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3 mb-4 ' . ($border ? 'pb-4 border-b border-slate-100' : '')]) }}>
    @if ($icon)
        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
        </div>
    @endif
    <div class="min-w-0">
        <h3 class="text-sm font-semibold text-slate-800 font-display">{{ $title }}</h3>
        @if ($subtitle)
            <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
</div>
