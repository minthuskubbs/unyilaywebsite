<x-layouts.app :categories="$categories" title="My Orders — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-account">
        <div class="unyl-account__header">
            <h1>My Orders</h1>
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
            @if (empty($orders))
                <p class="unyl-account__empty">You haven't placed any orders yet.</p>
            @else
                <table class="unyl-account__table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>#{{ $order['number'] ?? $order['id'] }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($order['date_created'])->format('M j, Y') }}</td>
                                <td><span class="unyl-account__status">{{ ucfirst($order['status']) }}</span></td>
                                <td>{{ \App\Support\Money::format($order['total']) }}</td>
                                <td><a href="{{ route('account.orders.show', $order['id']) }}">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </div>
</x-layouts.app>
