@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between mt-2">
    {{-- Previous Page Link --}}
    @if ($paginator->onFirstPage())
        <span class="px-3 py-1 text-gray-400">Sebelumnya</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"
           class="px-3 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200">
            Sebelumnya
        </a>
    @endif

    {{-- Pagination Elements --}}
    <div class="flex space-x-1">
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-3 py-1 text-gray-500">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1 bg-green-600 text-white rounded-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           class="px-3 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>

    {{-- Next Page Link --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           class="px-3 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200">
            Selanjutnya
        </a>
    @else
        <span class="px-3 py-1 text-gray-400">Selanjutnya</span>
    @endif
</nav>
@endif