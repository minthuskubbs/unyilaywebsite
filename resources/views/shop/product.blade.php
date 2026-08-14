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
                        <button type="submit" class="unyl-btn" id="addToCartBtn" {{ $product['type'] === 'variable' ? 'disabled' : '' }}>Add to cart</button>
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
