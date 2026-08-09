@if ($paginator->hasPages())
    <div class="p-6 border-t border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row items-center justify-between gap-4 font-display">
        <!-- Pagination Info -->
        @if (method_exists($paginator, 'firstItem') && $paginator->firstItem())
            <p class="text-xs text-slate-400 font-semibold">
                Showing 
                <span class="font-bold text-slate-700">{{ $paginator->firstItem() }}</span> 
                to 
                <span class="font-bold text-slate-700">{{ $paginator->lastItem() }}</span> 
                of 
                <span class="font-bold text-[#0056D2]">{{ $paginator->total() }}</span> 
                results
            </p>
        @elseif (method_exists($paginator, 'currentPage'))
            <p class="text-xs text-slate-400 font-semibold">
                Showing page <span class="font-bold text-slate-700">{{ $paginator->currentPage() }}</span>
            </p>
        @endif

        <!-- Pagination Navigation -->
        <nav aria-label="Page navigation" class="inline-flex items-center gap-1.5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 text-xs text-slate-300 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed font-semibold">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" 
                   class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-[#0056D2] bg-white hover:bg-slate-50 border border-slate-200/80 rounded-xl transition-all duration-150 shadow-sm">
                    Previous
                </a>
            @endif

            {{-- Page 1 --}}
            @if ($paginator->currentPage() == 1)
                <span class="px-3.5 py-1.5 text-xs font-bold bg-[#0056D2] text-white border border-[#0056D2] rounded-xl shadow-sm shadow-[#0056D2]/10">1</span>
            @else
                <a href="{{ $paginator->url(1) }}" 
                   class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-[#0056D2] bg-white hover:bg-slate-50 border border-slate-200/80 rounded-xl transition-all duration-150 shadow-sm">
                    1
                </a>
            @endif

            {{-- Page 2 --}}
            @if ($paginator->lastPage() >= 2)
                @if ($paginator->currentPage() == 2)
                    <span class="px-3.5 py-1.5 text-xs font-bold bg-[#0056D2] text-white border border-[#0056D2] rounded-xl shadow-sm shadow-[#0056D2]/10">2</span>
                @else
                    <a href="{{ $paginator->url(2) }}" 
                       class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-[#0056D2] bg-white hover:bg-slate-50 border border-slate-200/80 rounded-xl transition-all duration-150 shadow-sm">
                        2
                    </a>
                @endif
            @endif

            {{-- Intermediate Pages --}}
            @if ($paginator->currentPage() > 2 && $paginator->currentPage() < $paginator->lastPage())
                @if ($paginator->currentPage() > 3)
                    <span class="px-3.5 py-1.5 text-xs text-slate-400 cursor-default font-semibold">...</span>
                @endif

                <span class="px-3.5 py-1.5 text-xs font-bold bg-[#0056D2] text-white border border-[#0056D2] rounded-xl shadow-sm shadow-[#0056D2]/10">
                    {{ $paginator->currentPage() }}
                </span>
            @endif

            {{-- Last Page (if more than 2 pages) --}}
            @if ($paginator->lastPage() > 2)
                @if ($paginator->lastPage() > max(2, $paginator->currentPage()) + 1)
                    <span class="px-3.5 py-1.5 text-xs text-slate-400 cursor-default font-semibold">...</span>
                @endif

                @if ($paginator->currentPage() == $paginator->lastPage())
                    <span class="px-3.5 py-1.5 text-xs font-bold bg-[#0056D2] text-white border border-[#0056D2] rounded-xl shadow-sm shadow-[#0056D2]/10">{{ $paginator->lastPage() }}</span>
                @else
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" 
                       class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-[#0056D2] bg-white hover:bg-slate-50 border border-slate-200/80 rounded-xl transition-all duration-150 shadow-sm">
                        {{ $paginator->lastPage() }}
                    </a>
                @endif
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" 
                   class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-[#0056D2] bg-white hover:bg-slate-50 border border-slate-200/80 rounded-xl transition-all duration-150 shadow-sm">
                    Next
                </a>
            @else
                <span class="px-3 py-1.5 text-xs text-slate-400 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed font-semibold">Next</span>
            @endif
        </nav>
    </div>
@endif
