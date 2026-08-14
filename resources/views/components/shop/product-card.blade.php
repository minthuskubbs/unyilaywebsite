@props(['product'])

<a href="{{ url('/product/' . $product['slug']) }}" class="unyl-pcard">
    <div class="unyl-pcard__image">
        @if ($product['image'])
            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" />
        @endif
    </div>
    <div class="unyl-pcard__body">
        <p class="unyl-pcard__title">{{ $product['name'] }}</p>
        @if ($product['price'])
            <p class="unyl-pcard__price">{{ \App\Support\Money::range($product['price']) }}</p>
        @endif
    </div>
</a>
