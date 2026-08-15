<x-layouts.app :categories="$categories" :title="$product['name'] . ' — U Nyi Lay Silver Shop'" :body-class="($isJewelry ?? false) ? 'theme-light' : 'theme-product'">
    <div class="unyl-product">
        <x-shop.breadcrumb :breadcrumbs="array_slice($breadcrumbs, 0, -1)" :current="$product['name']" />

        <div class="unyl-product__layout">
            <div class="unyl-product__gallery">
                @if (count($product['images']))
                    <div class="unyl-product__main-image">
                        <img src="{{ $product['images'][0] }}" alt="{{ $product['name'] }}" id="productMainImageTag" />
                    </div>
                    @if (count($product['images']) > 1)
                        <div class="unyl-product__thumbs">
                            @foreach ($product['images'] as $i => $img)
                                <button type="button" class="unyl-product__thumb {{ $i === 0 ? 'is-active' : '' }}" data-image="{{ $img }}">
                                    <img src="{{ $img }}" alt="" />
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="unyl-product__main-image unyl-product__main-image--empty"></div>
                @endif
            </div>

            <div class="unyl-product__summary">
                <h1>{{ $product['name'] }}</h1>

                <p class="unyl-product__price" id="productPrice">{{ \App\Support\Money::range($product['price']) }}</p>

                @if (($isJewelry ?? false) && !empty($sizeCharts))
                    @foreach ($sizeCharts as $chart)
                        <div class="unyl-popup-trigger-wrap">
                            <button type="button" class="unyl-popup-trigger" data-modal-trigger="sizeChart{{ $chart['id'] }}">{{ $chart['button_text'] }}</button>
                        </div>

                        <div class="unyl-popup-modal" id="sizeChart{{ $chart['id'] }}">
                            <div class="unyl-popup-modal__overlay" data-modal-close></div>
                            <div class="unyl-popup-modal__panel">
                                <div class="unyl-popup-modal__header">
                                    <h3>{{ $chart['title'] }}</h3>
                                    <button type="button" class="unyl-popup-modal__close" data-modal-close aria-label="Close">&times;</button>
                                </div>

                                @if (!empty($chart['table']) && $chart['content'] !== '')
                                    <div class="unyl-popup-modal__tabs">
                                        <button type="button" class="unyl-popup-tab is-active" data-tab-trigger="chart" data-tab-group="sizeChart{{ $chart['id'] }}">{{ $chart['tab_title'] }}</button>
                                        <button type="button" class="unyl-popup-tab" data-tab-trigger="desc" data-tab-group="sizeChart{{ $chart['id'] }}">{{ $chart['desc_tab_title'] }}</button>
                                    </div>
                                @endif

                                <div class="unyl-popup-modal__body">
                                    @if (!empty($chart['table']))
                                        <div class="unyl-popup-tab-panel is-active" data-tab-panel="chart" data-tab-group="sizeChart{{ $chart['id'] }}">
                                            <div class="unyl-popup-table-scroll">
                                                <table class="unyl-popup-table">
                                                    <thead>
                                                        <tr>
                                                            @foreach ($chart['table'][0] as $cell)
                                                                <th>{{ $cell }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach (array_slice($chart['table'], 1) as $row)
                                                            <tr>
                                                                @foreach ($row as $cell)
                                                                    <td>{{ $cell }}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($chart['content'] !== '')
                                        <div class="unyl-popup-tab-panel {{ empty($chart['table']) ? 'is-active' : '' }}" data-tab-panel="desc" data-tab-group="sizeChart{{ $chart['id'] }}">
                                            <div class="unyl-popup-richtext">{!! $chart['content'] !!}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if (!($isJewelry ?? false) && $product['type'] === 'variable' && count($product['variations']))
                    <div class="unyl-popup-trigger-wrap">
                        <button type="button" class="unyl-popup-trigger" data-modal-trigger="priceChartsModal">Price Charts</button>
                    </div>

                    <div class="unyl-popup-modal" id="priceChartsModal">
                        <div class="unyl-popup-modal__overlay" data-modal-close></div>
                        <div class="unyl-popup-modal__panel">
                            <div class="unyl-popup-modal__header">
                                <h3>Price Charts</h3>
                                <button type="button" class="unyl-popup-modal__close" data-modal-close aria-label="Close">&times;</button>
                            </div>
                            <div class="unyl-popup-modal__body">
                                <div class="unyl-popup-table-scroll">
                                    <table class="unyl-popup-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($product['variations'] as $variation)
                                                @php
                                                    $parts = [];
                                                    foreach ($variation['attributes'] as $taxonomy => $value) {
                                                        if ($value === '') continue;
                                                        $label = trim(str_replace(['pa_', '-', '_'], ['', ' ', ' '], strtolower($taxonomy)));
                                                        $parts[] = $label . ': ' . $value;
                                                    }
                                                    $rowName = implode(', ', $parts);
                                                @endphp
                                                @if ($rowName !== '')
                                                    <tr>
                                                        <td>{{ $rowName }}</td>
                                                        <td>{{ \App\Support\Money::format($variation['price']) }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($product['short_description'])
                    <div class="unyl-product__short-desc">{!! nl2br(e(strip_tags($product['short_description']))) !!}</div>
                @endif

                <form class="unyl-product__form" id="productForm" method="POST" action="{{ url('/cart/add') }}" data-variations="{{ json_encode($product['variations']) }}">
                    @csrf

                    @if ($product['type'] === 'variable')
                        @foreach ($product['attributes'] as $taxonomy => $attr)
                            <div class="unyl-product__attr">
                                <label for="attr-{{ $taxonomy }}">{{ $attr['label'] }}</label>
                                <select id="attr-{{ $taxonomy }}" class="unyl-product__select" name="attributes[{{ $taxonomy }}]" data-attribute="{{ $taxonomy }}">
                                    <option value="">Choose an option</option>
                                    @foreach ($attr['terms'] as $term)
                                        <option value="{{ $term['slug'] }}">{{ $term['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    @endif

                    <div class="unyl-product__qty-row">
                        <div class="unyl-qty">
                            <button type="button" class="unyl-qty__btn" data-action="minus" aria-label="Decrease quantity">-</button>
                            <input type="number" class="unyl-qty__input" id="productQty" name="quantity" value="1" min="1" />
                            <button type="button" class="unyl-qty__btn" data-action="plus" aria-label="Increase quantity">+</button>
                        </div>
                        <button type="submit" class="unyl-medium-btn" id="addToCartBtn" {{ $product['type'] === 'variable' ? 'disabled' : '' }}>Add to cart</button>
                        <button
                            type="button"
                            class="unyl-product__wishlist {{ app(\App\Services\WishlistService::class)->has($product['id']) ? 'is-active' : '' }}"
                            data-wishlist-toggle
                            data-product-id="{{ $product['id'] }}"
                            aria-label="Add to wishlist"
                        >
                            <svg viewBox="0 0 21 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3806 17.7283L2.22677 10.6088C-2.20469 6.3371 4.30956 -1.86456 10.3806 4.77081C16.4518 -1.86456 22.9365 6.36558 18.5345 10.6088L10.3806 17.7283Z"/></svg>
                        </button>
                    </div>

                    <input type="hidden" name="product_id" value="{{ $product['id'] }}" />
                    <input type="hidden" name="variation_id" id="variationIdInput" value="0" />
                </form>

                <div class="unyl-product__meta">
                    @if ($product['sku'])
                        <span>SKU: {{ $product['sku'] }}</span>
                    @endif
                    @if (count($product['categories']))
                        <span>
                            Category:
                            @foreach ($product['categories'] as $cat)
                                <a href="{{ url('/product-category/' . $cat['slug']) }}">{{ $cat['name'] }}</a>{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </span>
                    @endif
                </div>

                @if ($product['description'])
                    <div class="unyl-product__description">
                        <h3>Description</h3>
                        {!! nl2br(e(strip_tags($product['description']))) !!}
                    </div>
                @endif
            </div>
        </div>

        <div class="unyl-product__related">
            <x-ui.product-slider :products="$related" title="Related products" :show-caption="true" />
        </div>
    </div>
</x-layouts.app>
