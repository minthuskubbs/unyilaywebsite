<x-layouts.app :categories="$categories" :title="$title . ' — U Nyi Lay Silver Shop'" :body-class="($isJewelry ?? false) ? 'theme-light' : null">
    <div class="unyl-shop">
        <div class="unyl-shop__header">
            <x-shop.breadcrumb :breadcrumbs="array_slice($breadcrumbs, 0, -1)" :current="$title" />
            <button type="button" class="unyl-shop-sidebar__mobile-trigger" id="openShopFilter">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                Categories
            </button>
        </div>

        <div class="unyl-shop__layout">
            <x-shop.sidebar :tree="$sidebar" :active-slug="!empty($breadcrumbs) ? end($breadcrumbs)['slug'] : null" />

            <div class="unyl-shop__main">
                @if (empty($listing['items']))
                    <p class="unyl-shop__empty">
                        @if (!empty($search))
                            No products found for "{{ $search }}".
                        @else
                            No products found in this category yet.
                        @endif
                    </p>
                @else
                    <div class="unyl-shop__grid">
                        @foreach ($listing['items'] as $product)
                            <x-shop.product-card :product="$product" />
                        @endforeach
                    </div>

                    <x-shop.pagination :listing="$listing" :base-url="$baseUrl" />
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
