@php
    $sub = $sub ?? false;
    $badge = $badge ?? null;
@endphp
<a href="{{ $href }}"
   title="{{ $label }}"
   @if($active) aria-current="page" @endif
   :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
   class="flex items-center {{ $sub ? 'gap-2.5 py-2 pl-3 pr-3 text-xs' : 'gap-3 py-2.5 px-3 text-sm' }} rounded-md font-medium font-display transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary {{ $active ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
    <span class="relative shrink-0">
        <i data-lucide="{{ $icon }}" class="{{ $sub ? 'w-3.5 h-3.5' : 'w-5 h-5' }} shrink-0 {{ $active ? 'text-slate-900' : 'text-slate-400' }}"></i>
        @if ($badge)
            <span x-show="sidebarCollapsed" x-cloak class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-rose-500"></span>
        @endif
    </span>
    <span x-show="!sidebarCollapsed" x-cloak class="flex-1">{{ $label }}</span>
    @if ($badge)
        <span x-show="!sidebarCollapsed" x-cloak class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-rose-500 text-white rounded-full font-display leading-none">{{ $badge }}</span>
    @endif
</a>
