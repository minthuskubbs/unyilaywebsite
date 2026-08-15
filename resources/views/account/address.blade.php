<x-layouts.app :categories="$categories" title="Billing Details — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-account">
        <div class="unyl-account__header">
            <h1>Billing Details</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="unyl-account__logout">Log out</button>
            </form>
        </div>

        <nav class="unyl-account__nav">
            <a href="{{ route('account.dashboard') }}">Dashboard</a>
            <a href="{{ route('account.orders') }}">Orders</a>
            <a href="{{ route('account.address') }}" class="is-active">Billing details</a>
        </nav>

        <section class="unyl-account__section unyl-account__section--narrow">
            @if (session('success'))
                <p class="unyl-contact__flash">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('account.address.update') }}">
                @csrf
                <div class="unyl-checkout__row">
                    <div class="unyl-field">
                        <label for="first_name">First name *</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $billing['first_name'] ?? '') }}" required />
                    </div>
                    <div class="unyl-field">
                        <label for="last_name">Last name *</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $billing['last_name'] ?? '') }}" required />
                    </div>
                </div>
                <div class="unyl-field">
                    <label for="address_1">Street address *</label>
                    <input type="text" id="address_1" name="address_1" value="{{ old('address_1', $billing['address_1'] ?? '') }}" required />
                </div>
                <div class="unyl-checkout__row">
                    <div class="unyl-field">
                        <label for="city">Town / City *</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $billing['city'] ?? '') }}" required />
                    </div>
                    <div class="unyl-field">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $billing['phone'] ?? '') }}" required />
                    </div>
                </div>
                <div class="unyl-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $billing['email'] ?? $customer['email']) }}" />
                </div>

                <button type="submit" class="unyl-btn">Save changes</button>
            </form>
        </section>
    </div>
</x-layouts.app>
