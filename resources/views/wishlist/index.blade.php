<x-layouts.app :categories="$categories" title="Wishlist — U Nyi Lay Silver Shop">
    <div class="unyl-cart">
        @if (empty($items))
            <div class="unyl-cart-empty">
                <svg viewBox="0 0 21 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" class="unyl-cart-empty__icon">
                    <path d="M10.3806 17.7283L2.22677 10.6088C-2.20469 6.3371 4.30956 -1.86456 10.3806 4.77081C16.4518 -1.86456 22.9365 6.36558 18.5345 10.6088L10.3806 17.7283Z"/>
                </svg>
                <p>No products added to the wishlist yet.</p>
                <a href="{{ url('/shop') }}" class="unyl-btn">Return to shop</a>
            </div>
        @else
            <h1 class="unyl-cart__title">Wishlist</h1>

            <div class="unyl-cart__table-wrap">
                <table class="unyl-cart__table">
                    <thead>
                        <tr>
                            <th colspan="2">Product</th>
                            <th>Price</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td class="unyl-cart__remove">
                                    <form action="{{ route('wishlist.remove', $item['id']) }}" method="POST">
                                        @csrf
                                        <button type="submit" aria-label="Remove item">&times;</button>
                                    </form>
                                </td>
                                <td class="unyl-cart__product">
                                    <a href="{{ url('/product/' . $item['slug']) }}" class="unyl-cart__thumb">
                                        @if ($item['image'])
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" />
                                        @endif
                                    </a>
                                    <div>
                                        <a href="{{ url('/product/' . $item['slug']) }}" class="unyl-cart__name">{{ $item['name'] }}</a>
                                    </div>
                                </td>
                                <td>{{ \App\Support\Money::range($item['price']) }}</td>
                                <td>
                                    <a href="{{ url('/product/' . $item['slug']) }}" class="unyl-btn unyl-btn--sm">View product</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.app>
