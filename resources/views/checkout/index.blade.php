<x-layouts.app :categories="$categories" title="Checkout — U Nyi Lay Silver Shop">
    <div class="unyl-checkout">
        <h1 class="unyl-checkout__title">Checkout</h1>

        @if ($errors->any())
            <div class="unyl-checkout__errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="unyl-checkout__layout">
            @csrf

            <div class="unyl-checkout__form">
                <h2>Billing &amp; Shipping</h2>

                <div class="unyl-checkout__row">
                    <div class="unyl-field">
                        <label for="billing_first_name">First name *</label>
                        <input type="text" id="billing_first_name" name="billing_first_name" value="{{ old('billing_first_name') }}" required />
                    </div>
                    <div class="unyl-field">
                        <label for="billing_last_name">Last name *</label>
                        <input type="text" id="billing_last_name" name="billing_last_name" value="{{ old('billing_last_name') }}" required />
                    </div>
                </div>

                <div class="unyl-field">
                    <label for="billing_country">Country / Region</label>
                    <input type="text" id="billing_country" value="Myanmar" disabled />
                </div>

                <div class="unyl-field">
                    <label for="billing_address_1">Street address *</label>
                    <input type="text" id="billing_address_1" name="billing_address_1" value="{{ old('billing_address_1') }}" placeholder="House number and street name" required />
                </div>

                <div class="unyl-checkout__row">
                    <div class="unyl-field">
                        <label for="billing_city">Town / City *</label>
                        <input type="text" id="billing_city" name="billing_city" value="{{ old('billing_city') }}" required />
                    </div>
                    <div class="unyl-field">
                        <label for="billing_phone">Phone *</label>
                        <input type="tel" id="billing_phone" name="billing_phone" value="{{ old('billing_phone') }}" required />
                    </div>
                </div>

                <div class="unyl-field">
                    <label for="order_comments">Order notes (optional)</label>
                    <textarea id="order_comments" name="order_comments" rows="4">{{ old('order_comments') }}</textarea>
                </div>

                <div class="unyl-checkout__note">Delivery Area: Yangon</div>
            </div>

            <div class="unyl-checkout__sidebar">
                <h2>Your order</h2>

                <table class="unyl-checkout__review">
                    <thead>
                        <tr><th>Product</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($cart['lines'] as $line)
                            <tr>
                                <td>
                                    {{ $line['name'] }} &times; {{ $line['qty'] }}
                                    @if ($line['variation_label'])
                                        <span class="unyl-checkout__variation">{{ $line['variation_label'] }}</span>
                                    @endif
                                </td>
                                <td>{{ \App\Support\Money::format($line['line_total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td>Subtotal</td><td>{{ \App\Support\Money::format($cart['subtotal']) }}</td></tr>
                        <tr class="unyl-checkout__total"><td>Total</td><td>{{ \App\Support\Money::format($cart['subtotal']) }}</td></tr>
                    </tfoot>
                </table>

                <div class="unyl-checkout__payment">
                    <label class="unyl-checkout__payment-option">
                        <input type="radio" name="payment_method" value="cod" checked />
                        <span>Cash on delivery</span>
                        <small>Pay with cash upon delivery.</small>
                    </label>
                </div>

                <label class="unyl-checkout__terms">
                    <input type="checkbox" name="terms" value="1" required />
                    I have read and agree to the website terms and conditions
                </label>

                <button type="submit" class="unyl-btn unyl-checkout__submit">Place order</button>
            </div>
        </form>
    </div>
</x-layouts.app>
