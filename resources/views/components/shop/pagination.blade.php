@props(['listing', 'baseUrl'])

@php
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
@endphp

@if ($listing['lastPage'] > 1)
    <nav class="unyl-pagination" aria-label="Product pagination">
        @if ($listing['currentPage'] > 1)
            <a href="{{ $baseUrl }}{{ $sep }}product-page={{ $listing['currentPage'] - 1 }}" class="unyl-pagination__arrow">&lsaquo;</a>
        @endif

        @for ($i = 1; $i <= $listing['lastPage']; $i++)
            <a href="{{ $baseUrl }}{{ $sep }}product-page={{ $i }}" class="{{ $i === $listing['currentPage'] ? 'is-active' : '' }}">{{ $i }}</a>
        @endfor

        @if ($listing['currentPage'] < $listing['lastPage'])
            <a href="{{ $baseUrl }}{{ $sep }}product-page={{ $listing['currentPage'] + 1 }}" class="unyl-pagination__arrow">&rsaquo;</a>
        @endif
    </nav>
@endif
