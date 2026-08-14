<x-layouts.app :categories="$categories" title="Contact Us — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-contact">
        <section class="unyl-contact__hero">
            <img src="https://unyilaysilver.com/wp-content/uploads/2020/07/shwedagon-pagoda-temple-beautiful-sunset-in-yangon-myanmar-or-burma_SD7WiN1unzl-scaled.jpg" alt="" class="unyl-contact__hero-bg" loading="lazy" />
            <div class="unyl-contact__hero-overlay"></div>
            <div class="unyl-contact__hero-content">
                <h1>Contact Us</h1>
                <p>Need the best quality silverwares?</p>
            </div>
        </section>

        <section class="unyl-contact__info">
            <div class="unyl-contact__col">
                <h4>Visit us</h4>
                <a href="https://www.google.com/maps/place/U+Nyi+Lay+Silver+Shop/@16.7803672,96.1534116,17z" target="_blank" rel="noopener">
                    No. (1), Soon Loon Gu Kyaung Street, Yankin Tsp, Yangon, Myanmar.
                </a>
                <a href="https://www.google.com/maps/place/U+Nyi+Lay+Silver+Shop/@16.7803672,96.1534116,17z" target="_blank" rel="noopener">
                    No. (62), Central Hall, Bogyoke Market.
                </a>
            </div>
            <div class="unyl-contact__col">
                <h4>Call us</h4>
                <a href="tel:0950062583">09 506-2583</a>
                <a href="tel:0950124920">09 512-4920</a>
                <a href="tel:0950099843">09 509-9843</a>
                <a href="tel:0950016665">09 501-6665</a>
            </div>
            <div class="unyl-contact__col">
                <h4>Email us</h4>
                <a href="mailto:unyilaysilver@gmail.com">unyilaysilver@gmail.com</a>
                <a href="mailto:support@unyilaysilver.com">support@unyilaysilver.com</a>
            </div>
        </section>

        <section class="unyl-contact__form-section">
            <h2>Send us a message</h2>

            @if (session('success'))
                <p class="unyl-contact__flash">{{ session('success') }}</p>
            @endif

            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('pages.contact.submit') }}" class="unyl-contact__form">
                @csrf
                <div class="unyl-checkout__row">
                    <div class="unyl-field">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required />
                    </div>
                    <div class="unyl-field">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required />
                    </div>
                </div>
                <div class="unyl-field">
                    <label for="phone">Phone (optional)</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" />
                </div>
                <div class="unyl-field">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="unyl-btn">Send message</button>
            </form>
        </section>
    </div>
</x-layouts.app>
