@props(['tree' => [], 'activeSlug' => null])

<button type="button" class="unyl-shop-sidebar__mobile-trigger" id="openShopFilter">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
    Categories
</button>

<div class="unyl-shop-sidebar__backdrop" id="shopFilterBackdrop"></div>

<aside class="unyl-shop-sidebar" id="shopSidebar">
    <div class="unyl-shop-sidebar__card">
        <div class="unyl-shop-sidebar__mobile-header">
            <h4 class="unyl-shop-sidebar__title">Categories</h4>
            <button type="button" class="unyl-shop-sidebar__close" id="closeShopFilter" aria-label="Close">&times;</button>
        </div>
        <ul class="unyl-shop-sidebar__list">
            @foreach ($tree as $cat)
                <li class="{{ $activeSlug === $cat['slug'] ? 'is-active' : '' }}">
                    <a href="{{ url('/product-category/' . $cat['slug']) }}">{{ $cat['name'] }}</a>
                    @if (!empty($cat['children']))
                        <ul>
                            @foreach ($cat['children'] as $child)
                                <li class="{{ $activeSlug === $child['slug'] ? 'is-active' : '' }}">
                                    <a href="{{ url('/product-category/' . $child['slug']) }}">{{ $child['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</aside>
