@if ($paginator->hasPages())
    <nav class="cust-paginate">
        {{-- Prev --}}
        @if ($paginator->onFirstPage())
            <span class="cust-page disabled"><i class='bx bx-chevron-left'></i></span>
        @else
            <a class="cust-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                <i class='bx bx-chevron-left'></i>
            </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="cust-page dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="cust-page active">{{ $page }}</span>
                    @else
                        <a class="cust-page" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a class="cust-page" href="{{ $paginator->nextPageUrl() }}" rel="next">
                <i class='bx bx-chevron-right'></i>
            </a>
        @else
            <span class="cust-page disabled"><i class='bx bx-chevron-right'></i></span>
        @endif
    </nav>
@endif
