@props(['products' => [], 'title' => null, 'showCaption' => false])

@if (count($products))
    <div class="unyl-slider" data-per-view-desktop="3" data-per-view-tablet="2" data-per-view-mobile="1">
        @if ($title)
            <h2 class="unyl-slider__title">{{ $title }}</h2>
        @endif

        <div class="unyl-slider__stage">
            <div class="unyl-slider__viewport">
                <div class="unyl-slider__track">
                    @foreach ($products as $product)
                        <a class="unyl-slider__slide" href="{{ url('/product/' . $product['slug']) }}">
                            <div class="unyl-slider__slide-image">
                                @if ($product['image'])
                                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" />
                                @endif
                            </div>
                            @if ($showCaption)
                                <p class="unyl-slider__slide-title">{{ $product['name'] }}</p>
                                @if (!empty($product['price']))
                                    <p class="unyl-slider__slide-price">{{ \App\Support\Money::range($product['price']) }}</p>
                                @endif
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            <button class="unyl-slider-arrow prev" type="button" aria-label="Previous">&lsaquo;</button>
            <button class="unyl-slider-arrow next" type="button" aria-label="Next">&rsaquo;</button>
        </div>

        <div class="unyl-slider-dots"></div>
    </div>
@endif
