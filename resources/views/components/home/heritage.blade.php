<section class="unyl-heritage">
    <h2 class="unyl-heritage__title">Enjoy the beauty of heritage</h2>

    <div class="unyl-heritage__grid">
        <div class="unyl-heritage__col">
            <a href="{{ url('/shop') }}" class="unyl-hcard unyl-hcard--lg unyl-hcard--big-items" style="--bg-desktop:url('{{ asset('images/home/big-items-block.jpg') }}'); --bg-mobile:url('{{ asset('images/home/big-items-block.jpg') }}')">
                <div class="unyl-hcard__cap">
                    <h3>Big Items (Silver)</h3>
                    <p>Our premium silverware is perfect for gifts and decorative purposes.</p>
                    <span class="unyl-btn">Shop Now</span>
                </div>
            </a>
            <a href="{{ url('/brass') }}" class="unyl-hcard unyl-hcard--sm unyl-hcard--brass" style="--bg-desktop:url('{{ asset('images/home/brass-block.jpg') }}'); --bg-mobile:url('{{ asset('images/home/brass-block.jpg') }}')">
                <div class="unyl-hcard__cap">
                    <h3>Brass</h3>
                    <p>Experience masterful craftsmanship combined with accessible elegance.</p>
                    <span class="unyl-btn">Shop Now</span>
                </div>
            </a>
        </div>

        <div class="unyl-heritage__col">
            <div class="unyl-hcard unyl-hcard--sm unyl-hcard--plain unyl-hcard--quote">
                <div class="unyl-hcard__cap">
                    <p class="unyl-hcard__lead">Timeless Burmese artistry. Uncompromising silver quality. A legacy of over 60 years</p>
                </div>
            </div>
            <a href="{{ url('/product-category/silver-jewelry') }}" class="unyl-hcard unyl-hcard--lg unyl-hcard--jewelry" style="--bg-desktop:url('{{ asset('images/home/jewelry-block.jpg') }}'); --bg-mobile:url('{{ asset('images/home/jewelry-block.jpg') }}')">
                <div class="unyl-hcard__cap">
                    <h3>Silver Jewelry</h3>
                    <p>These luxurious silver pieces truly define your personal style.</p>
                    <span class="unyl-btn">Shop Now</span>
                </div>
            </a>
        </div>
    </div>

    <div class="unyl-divider">
        <img src="{{ asset('images/home/divider-leaf-ornament.svg') }}" alt="" loading="lazy" />
    </div>
</section>
