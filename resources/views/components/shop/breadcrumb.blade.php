@props(['breadcrumbs' => [], 'current' => null, 'hideOnMobile' => false])

<nav class="unyl-breadcrumb {{ $hideOnMobile ? 'unyl-breadcrumb--hide-mobile' : '' }}" aria-label="Breadcrumb">
    <button type="button" class="unyl-breadcrumb__trigger" id="breadcrumbTrigger" aria-label="Show page path">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6l5 6-5 6"/><path d="M10 6l5 6-5 6"/><path d="M17 6l5 6-5 6"/></svg>
    </button>

    <div class="unyl-breadcrumb__trail" id="breadcrumbTrail">
        <a href="{{ url('/') }}">Home</a>
        @foreach ($breadcrumbs as $crumb)
            <span class="unyl-breadcrumb__sep">/</span>
            <a href="{{ $crumb['url'] ?? url('/product-category/' . $crumb['slug']) }}">{{ $crumb['name'] }}</a>
        @endforeach
        @if ($current)
            <span class="unyl-breadcrumb__sep">/</span>
            <span class="unyl-breadcrumb__current">{{ $current }}</span>
        @endif
    </div>
</nav>
