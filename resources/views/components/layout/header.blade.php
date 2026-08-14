@props(['categories' => []])

<header class="site-header">
    <div class="site-header__inner">
        <a href="{{ url('/') }}" class="site-header__logo">
            <img src="https://unyilaysilver.com/wp-content/uploads/2021/07/unyilay-logo.png" alt="U Nyi Lay Silver Shop" />
        </a>

        <ul class="site-header__nav">
            <li><a href="#" class="unyl-menu-trigger">Shop</a></li>
            <li><a href="{{ url('/about-us') }}">About Us</a></li>
            <li><a href="{{ url('/news-articles') }}">News &amp; Articles</a></li>
            <li><a href="{{ url('/contact-us') }}">Contact Us</a></li>
        </ul>

        <ul class="site-header__icons">
            <li>
                <a href="#" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                </a>
            </li>
            <li>
                <a href="{{ url('/wishlist') }}" aria-label="Wishlist">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-4.4-9.5-8.6C.7 8.8 2.2 5 6 5c2 0 3.4 1.1 4 2.1.6-1 2-2.1 4-2.1 3.8 0 5.3 3.8 3.5 7.4C19 16.6 12 21 12 21z"/></svg>
                </a>
            </li>
            <li>
                <a href="{{ url('/my-account') }}" aria-label="My account">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                </a>
            </li>
            <li class="site-header__icons--cart">
                <a href="{{ url('/cart') }}" aria-label="Cart">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                    <span class="site-header__cart-count">{{ app(\App\Services\CartService::class)->count() }}</span>
                </a>
            </li>
        </ul>

        <button type="button" class="site-header__mobile-toggle" id="openMobileMenu" aria-label="Open menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>

        {{-- Desktop mega menu --}}
        <div class="unyl-menu-shop" id="unylMenuShop">
            <div class="unyl-menu-panel-wrap">
                {{-- Stage 1: group list only --}}
                <div class="unyl-menu-stage1" id="unylMenuStage1">
                    @foreach ($categories as $group)
                        <button type="button" class="unyl-menu-row" data-unyl-group="{{ $group['key'] }}">
                            <span class="unyl-menu-row__thumb">
                                @if ($group['image'])
                                    <img src="{{ $group['image'] }}" alt="" loading="lazy" />
                                @endif
                            </span>
                            <span class="unyl-menu-row__text">
                                <span class="unyl-menu-row__title">{{ $group['name'] }}</span>
                                <span class="unyl-menu-row__desc">{{ $group['description'] }}</span>
                            </span>
                            <span class="unyl-menu-row__arrow">&rarr;</span>
                        </button>
                    @endforeach
                </div>

                {{-- Stage 2: expanded 3-column panel, one per group --}}
                @foreach ($categories as $group)
                    <div class="unyl-menu-stage2" data-unyl-stage2="{{ $group['key'] }}">
                        <div class="unyl-menu-stage2__list">
                            @foreach ($categories as $g)
                                <button type="button" class="unyl-menu-row unyl-menu-row--condensed {{ $g['key'] === $group['key'] ? 'is-active' : '' }}" data-unyl-group="{{ $g['key'] }}">
                                    <span class="unyl-menu-row__text">
                                        <span class="unyl-menu-row__title">{{ $g['name'] }}</span>
                                        <span class="unyl-menu-row__desc">{{ $g['description'] }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <div class="unyl-menu-stage2__middle">
                            <a class="unyl-menu-card unyl-menu-card--all" href="{{ $group['url'] }}">
                                <span class="unyl-menu-card__label">All Items</span>
                                @if ($group['image'])
                                    <img src="{{ $group['image'] }}" alt="" loading="lazy" />
                                @endif
                            </a>
                            <div class="unyl-menu-stage2__teasers">
                                @if ($group['popular_product'])
                                    <a class="unyl-menu-card" href="{{ url('/product/' . $group['popular_product']['slug']) }}">
                                        <span class="unyl-menu-card__label">Popular</span>
                                        @if ($group['popular_product']['image'])
                                            <img src="{{ $group['popular_product']['image'] }}" alt="" loading="lazy" />
                                        @endif
                                    </a>
                                @endif
                                @if ($group['new_product'])
                                    <a class="unyl-menu-card" href="{{ url('/product/' . $group['new_product']['slug']) }}">
                                        <span class="unyl-menu-card__label">New Items</span>
                                        @if ($group['new_product']['image'])
                                            <img src="{{ $group['new_product']['image'] }}" alt="" loading="lazy" />
                                        @endif
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="unyl-menu-stage2__categories">
                            <span class="unyl-menu-stage2__categories-label">Categories</span>
                            @foreach ($group['categories'] as $cat)
                                <a href="{{ url('/product-category/' . $cat['slug']) }}">{{ $cat['name'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</header>

{{-- Mobile menu --}}
<div class="unyl-menu-mobile" id="unylMobileMenu">
    <div class="unyl-menu-mobile-header">
        <a href="{{ url('/') }}" class="unyl-menu-mobile-logo">
            <img src="https://unyilaysilver.com/wp-content/uploads/2021/07/unyilay-logo.png" alt="U Nyi Lay Silver Shop" />
        </a>
        <button type="button" class="unyl-menu-mobile-close" aria-label="Close menu">&times;</button>
    </div>

    <div class="unyl-menu-mobile-list is-active" id="unylMobileList">
        @foreach ($categories as $group)
            <button type="button" class="unyl-menu-mobile-row" data-unyl-group="{{ $group['key'] }}">
                <span class="unyl-menu-mobile-row__img">
                    @if ($group['image'])
                        <img src="{{ $group['image'] }}" alt="" loading="lazy" />
                    @endif
                </span>
                <span>
                    <span class="unyl-menu-mobile-row__title">{{ $group['name'] }}</span>
                    <span class="unyl-menu-mobile-row__desc">{{ $group['description'] }}</span>
                </span>
            </button>
        @endforeach

        <div class="unyl-menu-mobile-links">
            <a href="{{ url('/about-us') }}">About us</a>
            <a href="{{ url('/news-articles') }}">News &amp; Articles</a>
            <a href="{{ url('/contact-us') }}">Contact Us</a>
        </div>
    </div>

    @foreach ($categories as $group)
        <div class="unyl-menu-mobile-detail" data-unyl-detail="{{ $group['key'] }}">
            <div class="unyl-menu-mobile-header">
                <button type="button" class="unyl-menu-mobile-back" aria-label="Back">&larr;</button>
                <span class="unyl-menu-mobile-title">{{ $group['name'] }}</span>
                <button type="button" class="unyl-menu-mobile-close" aria-label="Close menu">&times;</button>
            </div>

            <div class="unyl-menu-mobile-teasers">
                @if ($group['popular_product'])
                    <a class="unyl-menu-card" href="{{ url('/product/' . $group['popular_product']['slug']) }}">
                        <span class="unyl-menu-card__label">Popular</span>
                        @if ($group['popular_product']['image'])
                            <img src="{{ $group['popular_product']['image'] }}" alt="" loading="lazy" />
                        @endif
                    </a>
                @endif
                @if ($group['new_product'])
                    <a class="unyl-menu-card" href="{{ url('/product/' . $group['new_product']['slug']) }}">
                        <span class="unyl-menu-card__label">New Items</span>
                        @if ($group['new_product']['image'])
                            <img src="{{ $group['new_product']['image'] }}" alt="" loading="lazy" />
                        @endif
                    </a>
                @endif
            </div>

            <div class="unyl-menu-mobile-categories">
                <span class="unyl-menu-mobile-categories__label">Categories</span>
                @foreach ($group['categories'] as $cat)
                    <a href="{{ url('/product-category/' . $cat['slug']) }}">{{ $cat['name'] }}</a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
