@php
    $sub = $sub ?? false;
@endphp
<a href="{{ $href }}"
   title="{{ $label }}"
   @if($active) aria-current="page" @endif
   :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
   class="flex items-center {{ $sub ? 'gap-3 py-2 pl-4 pr-3 text-xs' : 'gap-3 py-2.5 px-3 text-sm' }} rounded-md font-medium font-display transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary {{ $active ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
    <i data-lucide="{{ $icon }}" class="{{ $sub ? 'w-4 h-4' : 'w-5 h-5' }} shrink-0 {{ $active ? 'text-slate-900' : 'text-slate-400' }}"></i>
    <span x-show="!sidebarCollapsed" x-cloak>{{ $label }}</span>
</a>
