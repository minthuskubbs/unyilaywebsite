<x-layouts.app :categories="$categories" title="Cart — U Nyi Lay Silver Shop">
    <div class="unyl-cart">
        @if (session('success'))
            <p class="unyl-cart__flash">{{ session('success') }}</p>
        @endif

        @if (empty($cart['lines']))
            <div class="unyl-cart-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="unyl-cart-empty__icon">
                    <path d="M3 4h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
                </svg>
                <p>Your cart is currently empty.</p>
                <a href="{{ url('/shop') }}" class="unyl-btn">Return to shop</a>
            </div>
        @else
            <h1 class="unyl-cart__title">Cart</h1>

            <div class="unyl-cart__layout">
                <form action="{{ route('cart.update') }}" method="POST" class="unyl-cart__table-wrap">
                    @csrf
                    <table class="unyl-cart__table">
                        <thead>
                            <tr>
                                <th colspan="2">Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cart['lines'] as $line)
                                <tr>
                                    <td class="unyl-cart__remove">
                                        <form action="{{ route('cart.remove', $line['key']) }}" method="POST">
                                            @csrf
                                            <button type="submit" aria-label="Remove item">&times;</button>
                                        </form>
                                    </td>
                                    <td class="unyl-cart__product">
                                        <a href="{{ url('/product/' . $line['slug']) }}" class="unyl-cart__thumb">
                                            @if ($line['image'])
                                                <img src="{{ $line['image'] }}" alt="{{ $line['name'] }}" />
                                            @endif
                                        </a>
                                        <div>
                                            <a href="{{ url('/product/' . $line['slug']) }}" class="unyl-cart__name">{{ $line['name'] }}</a>
                                            @if ($line['variation_label'])
                                                <span class="unyl-cart__variation">{{ $line['variation_label'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ \App\Support\Money::format($line['price']) }}</td>
                                    <td>
                                        <input type="number" class="unyl-cart__qty" name="qty[{{ $line['key'] }}]" value="{{ $line['qty'] }}" min="1" />
                                    </td>
                                    <td>{{ \App\Support\Money::format($line['line_total']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="unyl-cart__actions">
                        <a href="{{ url('/shop') }}" class="unyl-btn unyl-btn--dark">Continue shopping</a>
                        <button type="submit" class="unyl-btn">Update cart</button>
                    </div>
                </form>

                <div class="unyl-cart__totals">
                    <h3>Cart totals</h3>
                    <div class="unyl-cart__totals-row">
                        <span>Subtotal</span>
                        <span>{{ \App\Support\Money::format($cart['subtotal']) }}</span>
                    </div>
                    <p class="unyl-cart__totals-note">Shipping will be calculated at checkout.</p>
                    <div class="unyl-cart__totals-row unyl-cart__totals-row--total">
                        <span>Total</span>
                        <span>{{ \App\Support\Money::format($cart['subtotal']) }}</span>
                    </div>
                    <a href="{{ url('/checkout') }}" class="unyl-btn unyl-cart__checkout-btn">Proceed to checkout</a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
