<x-layouts.app :categories="$categories" :title="'Order #' . ($order['number'] ?? $order['id']) . ' — U Nyi Lay Silver Shop'">
    <div class="unyl-page unyl-account">
        <div class="unyl-account__header">
            <h1>Order #{{ $order['number'] ?? $order['id'] }}</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="unyl-account__logout">Log out</button>
            </form>
        </div>

        <nav class="unyl-account__nav">
            <a href="{{ route('account.dashboard') }}">Dashboard</a>
            <a href="{{ route('account.orders') }}" class="is-active">Orders</a>
            <a href="{{ route('account.address') }}">Billing details</a>
        </nav>

        <section class="unyl-account__section">
            <div class="unyl-account__order-meta">
                <div><span>Date</span><strong>{{ \Illuminate\Support\Carbon::parse($order['date_created'])->format('M j, Y') }}</strong></div>
                <div><span>Status</span><strong>{{ ucfirst($order['status']) }}</strong></div>
                <div><span>Payment method</span><strong>{{ $order['payment_method_title'] ?? '—' }}</strong></div>
            </div>

            <table class="unyl-account__table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order['line_items'] ?? [] as $line)
                        <tr>
                            <td>{{ $line['name'] }}</td>
                            <td>{{ $line['quantity'] }}</td>
                            <td>{{ \App\Support\Money::format($line['total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @if (!empty($order['shipping_total']) && (float) $order['shipping_total'] > 0)
                        <tr><td colspan="2">Shipping</td><td>{{ \App\Support\Money::format($order['shipping_total']) }}</td></tr>
                    @endif
                    <tr class="unyl-account__order-total"><td colspan="2">Total</td><td>{{ \App\Support\Money::format($order['total']) }}</td></tr>
                </tfoot>
            </table>

            @if (!empty($order['billing']))
                <div class="unyl-account__billing">
                    <h3>Billing address</h3>
                    <p>
                        {{ $order['billing']['first_name'] ?? '' }} {{ $order['billing']['last_name'] ?? '' }}<br>
                        {{ $order['billing']['address_1'] ?? '' }}<br>
                        {{ $order['billing']['city'] ?? '' }}<br>
                        @if (!empty($order['billing']['phone']))
                            {{ $order['billing']['phone'] }}
                        @endif
                    </p>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
