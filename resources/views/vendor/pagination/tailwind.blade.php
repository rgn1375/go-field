@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-gray-400 bg-white border-2 border-gray-200 cursor-not-allowed rounded-xl transition-all duration-300">
                <i class="ai-chevron-left text-lg"></i>
                <span class="hidden sm:inline">Previous</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-200 rounded-xl hover:border-primary hover:text-primary hover:shadow-lg hover:scale-105 transition-all duration-300">
                <i class="ai-chevron-left text-lg"></i>
                <span class="hidden sm:inline">Previous</span>
            </a>
        @endif

        {{-- Pagination Elements --}}
        <div class="flex items-center gap-2">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-11 h-11 text-gray-400 font-semibold">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center w-11 h-11 text-sm font-bold text-white bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg transform scale-110 transition-all duration-300">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center w-11 h-11 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-200 rounded-xl hover:border-primary hover:text-primary hover:shadow-lg hover:scale-110 transition-all duration-300">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-200 rounded-xl hover:border-primary hover:text-primary hover:shadow-lg hover:scale-105 transition-all duration-300">
                <span class="hidden sm:inline">Next</span>
                <i class="ai-chevron-right text-lg"></i>
            </a>
        @else
            <span class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-gray-400 bg-white border-2 border-gray-200 cursor-not-allowed rounded-xl transition-all duration-300">
                <span class="hidden sm:inline">Next</span>
                <i class="ai-chevron-right text-lg"></i>
            </span>
        @endif
    </nav>
@endif

