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
            <li class="site-header__icons--search">
                <button type="button" class="unyl-search-trigger" id="openSearch" aria-label="Search">
                    <svg viewBox="0 0 21 20" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M15.2058 14.6574L19.0221 18.3362M9.62223 16.8864C5.26143 16.8864 1.72632 13.4787 1.72632 9.27517C1.72632 5.07158 5.26143 1.66392 9.62223 1.66392C13.983 1.66392 17.5181 5.07158 17.5181 9.27517C17.5181 13.4787 13.983 16.8864 9.62223 16.8864Z"/></svg>
                </button>
            </li>
            <li>
                <a href="{{ url('/wishlist') }}" aria-label="Wishlist">
                    <svg viewBox="0 0 21 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3806 17.7283L2.22677 10.6088C-2.20469 6.3371 4.30956 -1.86456 10.3806 4.77081C16.4518 -1.86456 22.9365 6.36558 18.5345 10.6088L10.3806 17.7283Z"/></svg>
                    <span class="site-header__cart-count" id="wishlistCount" @if(app(\App\Services\WishlistService::class)->count() === 0) style="display:none;" @endif>{{ app(\App\Services\WishlistService::class)->count() }}</span>
                </a>
            </li>
            <li>
                <a href="{{ session('customer') ? url('/my-account') : url('/login') }}" aria-label="My account">
                    <svg viewBox="0 0 21 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7.31803 8.77956C6.50741 7.99816 6.052 6.93835 6.052 5.83328C6.052 4.72821 6.50741 3.6684 7.31803 2.887C8.12866 2.1056 9.22811 1.66661 10.3745 1.66661C11.5209 1.66661 12.6204 2.1056 13.431 2.887C14.2416 3.6684 14.697 4.72821 14.697 5.83328C14.697 6.93835 14.2416 7.99816 13.431 8.77956C12.6204 9.56096 11.5209 9.99995 10.3745 9.99995C9.22811 9.99995 8.12866 9.56096 7.31803 8.77956Z"/><path d="M3.45703 18.3334C3.45703 16.5653 4.18568 14.8696 5.48268 13.6193C6.77969 12.3691 8.5388 11.6667 10.373 11.6667C12.2073 11.6667 13.9664 12.3691 15.2634 13.6193C16.5604 14.8696 17.289 16.5653 17.289 18.3334H3.45703Z"/></svg>
                </a>
            </li>
            <li class="site-header__icons--cart">
                <a href="{{ url('/cart') }}" aria-label="Cart">
                    <svg viewBox="0 0 21 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.48787 17.7775C3.4227 17.6258 3.38916 17.4633 3.38916 17.2991C3.38916 17.135 3.4227 16.9724 3.48787 16.8208C3.55304 16.6691 3.64856 16.5313 3.76897 16.4152C3.88938 16.2992 4.03234 16.2071 4.18967 16.1443C4.347 16.0815 4.51562 16.0491 4.68591 16.0491C4.8562 16.0491 5.02483 16.0815 5.18216 16.1443C5.33949 16.2071 5.48244 16.2992 5.60285 16.4152C5.72327 16.5313 5.81879 16.6691 5.88395 16.8208C5.94912 16.9724 5.98266 17.135 5.98266 17.2991C5.98266 17.4633 5.94912 17.6258 5.88395 17.7775C5.81879 17.9291 5.72327 18.0669 5.60285 18.183C5.48244 18.2991 5.33949 18.3912 5.18216 18.454C5.02483 18.5168 4.8562 18.5491 4.68591 18.5491C4.51562 18.5491 4.347 18.5168 4.18967 18.454C4.03234 18.3912 3.88938 18.2991 3.76897 18.183C3.64856 18.0669 3.55304 17.9291 3.48787 17.7775Z"/><path d="M13.2782 18.183C13.0351 17.9486 12.8984 17.6306 12.8984 17.2991C12.8984 16.9676 13.0351 16.6497 13.2782 16.4152C13.5214 16.1808 13.8513 16.0491 14.1952 16.0491C14.5391 16.0491 14.8689 16.1808 15.1121 16.4152C15.3553 16.6497 15.4919 16.9676 15.4919 17.2991C15.4919 17.6306 15.3553 17.9486 15.1121 18.183C14.8689 18.4174 14.5391 18.5491 14.1952 18.5491C13.8513 18.5491 13.5214 18.4174 13.2782 18.183Z"/><path d="M20.2468 1.88248H17.9991C17.9016 1.88245 17.8069 1.91421 17.7305 1.97261C17.654 2.03101 17.6003 2.11261 17.5781 2.20415L14.1953 16.0491H4.68579"/><path d="M14.6282 14.3825H3.70092C3.61024 14.3825 3.52188 14.3549 3.44834 14.3038C3.3748 14.2526 3.31981 14.1805 3.29115 14.0975L0.985528 7.43087C0.963873 7.3682 0.957987 7.30146 0.968355 7.23616C0.978722 7.17087 1.00505 7.10888 1.04516 7.05532C1.08527 7.00176 1.13801 6.95816 1.19904 6.92812C1.26007 6.89808 1.32764 6.88245 1.39617 6.88253H16.4108"/></svg>
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

{{-- Full-screen search overlay --}}
<div class="unyl-search-overlay" id="unylSearchOverlay">
    <button type="button" class="unyl-search-overlay__close" id="closeSearch" aria-label="Close search">&times;</button>
    <div class="unyl-search-overlay__inner">
        <form class="unyl-search-form" id="unylSearchForm" action="{{ url('/shop') }}" method="GET" autocomplete="off">
            <input type="text" name="s" id="unylSearchInput" class="unyl-search-form__input" placeholder="Search Items" />
            <button type="submit" class="unyl-search-form__submit" aria-label="Submit search">
                <svg viewBox="0 0 21 20" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M15.2058 14.6574L19.0221 18.3362M9.62223 16.8864C5.26143 16.8864 1.72632 13.4787 1.72632 9.27517C1.72632 5.07158 5.26143 1.66392 9.62223 1.66392C13.983 1.66392 17.5181 5.07158 17.5181 9.27517C17.5181 13.4787 13.983 16.8864 9.62223 16.8864Z"/></svg>
            </button>
        </form>

        <div class="unyl-search-results" id="unylSearchResults"></div>
    </div>
</div>

{{-- Mobile-only floating cart button (chatbot-style bubble, bottom-right) --}}
<a href="{{ url('/cart') }}" class="unyl-mobile-cart-fab" aria-label="Cart">
    <svg viewBox="0 0 21 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.48787 17.7775C3.4227 17.6258 3.38916 17.4633 3.38916 17.2991C3.38916 17.135 3.4227 16.9724 3.48787 16.8208C3.55304 16.6691 3.64856 16.5313 3.76897 16.4152C3.88938 16.2992 4.03234 16.2071 4.18967 16.1443C4.347 16.0815 4.51562 16.0491 4.68591 16.0491C4.8562 16.0491 5.02483 16.0815 5.18216 16.1443C5.33949 16.2071 5.48244 16.2992 5.60285 16.4152C5.72327 16.5313 5.81879 16.6691 5.88395 16.8208C5.94912 16.9724 5.98266 17.135 5.98266 17.2991C5.98266 17.4633 5.94912 17.6258 5.88395 17.7775C5.81879 17.9291 5.72327 18.0669 5.60285 18.183C5.48244 18.2991 5.33949 18.3912 5.18216 18.454C5.02483 18.5168 4.8562 18.5491 4.68591 18.5491C4.51562 18.5491 4.347 18.5168 4.18967 18.454C4.03234 18.3912 3.88938 18.2991 3.76897 18.183C3.64856 18.0669 3.55304 17.9291 3.48787 17.7775Z"/><path d="M13.2782 18.183C13.0351 17.9486 12.8984 17.6306 12.8984 17.2991C12.8984 16.9676 13.0351 16.6497 13.2782 16.4152C13.5214 16.1808 13.8513 16.0491 14.1952 16.0491C14.5391 16.0491 14.8689 16.1808 15.1121 16.4152C15.3553 16.6497 15.4919 16.9676 15.4919 17.2991C15.4919 17.6306 15.3553 17.9486 15.1121 18.183C14.8689 18.4174 14.5391 18.5491 14.1952 18.5491C13.8513 18.5491 13.5214 18.4174 13.2782 18.183Z"/><path d="M20.2468 1.88248H17.9991C17.9016 1.88245 17.8069 1.91421 17.7305 1.97261C17.654 2.03101 17.6003 2.11261 17.5781 2.20415L14.1953 16.0491H4.68579"/><path d="M14.6282 14.3825H3.70092C3.61024 14.3825 3.52188 14.3549 3.44834 14.3038C3.3748 14.2526 3.31981 14.1805 3.29115 14.0975L0.985528 7.43087C0.963873 7.3682 0.957987 7.30146 0.968355 7.23616C0.978722 7.17087 1.00505 7.10888 1.04516 7.05532C1.08527 7.00176 1.13801 6.95816 1.19904 6.92812C1.26007 6.89808 1.32764 6.88245 1.39617 6.88253H16.4108"/></svg>
    @if (app(\App\Services\CartService::class)->count() > 0)
        <span class="unyl-mobile-cart-fab__count">{{ app(\App\Services\CartService::class)->count() }}</span>
    @endif
</a>

{{-- Mobile menu --}}
<div class="unyl-menu-mobile" id="unylMobileMenu">
    <div class="unyl-menu-mobile-header">
        <a href="{{ url('/') }}" class="unyl-menu-mobile-logo">
            <img src="https://unyilaysilver.com/wp-content/uploads/2021/07/unyilay-logo.png" alt="U Nyi Lay Silver Shop" />
        </a>
        <button type="button" class="unyl-menu-mobile-close" aria-label="Close menu">&times;</button>
    </div>

    <div class="unyl-menu-mobile-panels">
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
</div>
