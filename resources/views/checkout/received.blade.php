<x-layouts.app :categories="$categories" title="Order Received — U Nyi Lay Silver Shop">
    <div class="unyl-order-received">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="unyl-order-received__icon">
            <circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/>
        </svg>

        <h1>Thank you. Your order has been received.</h1>

        @if (!empty($order))
            <div class="unyl-order-received__summary">
                <div><span>Order number</span><strong>{{ $order['number'] ?? $order['id'] }}</strong></div>
                <div><span>Total</span><strong>{{ \App\Support\Money::format($order['total'] ?? 0) }}</strong></div>
                <div><span>Payment method</span><strong>{{ $order['payment_method_title'] ?? 'Cash on Delivery' }}</strong></div>
            </div>
        @endif

        <a href="{{ url('/shop') }}" class="unyl-btn">Continue shopping</a>
    </div>
</x-layouts.app>
