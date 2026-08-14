@props(['breadcrumbs' => [], 'current' => null])

<nav class="unyl-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ url('/') }}">Home</a>
    @foreach ($breadcrumbs as $crumb)
        <span class="unyl-breadcrumb__sep">/</span>
        <a href="{{ url('/product-category/' . $crumb['slug']) }}">{{ $crumb['name'] }}</a>
    @endforeach
    @if ($current)
        <span class="unyl-breadcrumb__sep">/</span>
        <span class="unyl-breadcrumb__current">{{ $current }}</span>
    @endif
</nav>
