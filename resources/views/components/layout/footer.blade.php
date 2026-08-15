<footer class="site-footer">
    <img src="{{ asset('images/home/footer-top-texture.png') }}" alt="" class="unyl-footer-top-texture" loading="lazy" />
    <div class="unyl-footer-card">
        <div class="unyl-footer-row">
            <div class="unyl-footer-block unyl-footer-block--intro">
                <h2>WANT TO<br>KNOW MORE?</h2>
                <a target="_blank" rel="noopener" href="https://www.facebook.com/unyilaysilvershop" class="unyl-footer-btn">
                    Follow us on Facebook
                </a>
            </div>

            <div class="unyl-footer-block">
                <h4>Site Map</h4>
                <p><a href="{{ url('/shop') }}">Shop</a></p>
                <p><a href="{{ url('/about-us') }}">About Us</a></p>
            </div>

            <div class="unyl-footer-block">
                <h4>Address</h4>
                <p>No. (1), Soon Loon Gu Kyaung Street, Yankin Tsp, Yangon, Myanmar.</p>
                <p>No. (62), Central Hall, Bogyoke Market.</p>
            </div>

            <div class="unyl-footer-block">
                <h4>Phone</h4>
                <p>
                    <a href="tel:095062583">09 506-2583</a>,<a href="tel:095124920">09 512-4920</a><br>
                    <a href="tel:095099843">09 509-9843</a>,<a href="tel:095016665">09 501-6665</a>.
                    
                </p>
            </div>

            <div class="unyl-footer-block">
                <h4>Email</h4>
                <a href="mailto:unyilaysilver@gmail.com">unyilaysilver@gmail.com</a>
                <a href="mailto:support@unyilaysilver.com">support@unyilaysilver.com</a>
            </div>
        </div>

        <div class="unyl-footer-bottom">
            <img src="{{ asset('images/home/group-15.svg') }}" alt="" class="unyl-footer-ornament" loading="lazy" />
            <p class="unyl-footer-copyright">Copyright {{ date('Y') }} &copy; U Nyi Lay Silver Shop</p>
            <img src="{{ asset('images/home/group-16.svg') }}" alt="" class="unyl-footer-ornament" loading="lazy" />
        </div>
    </div>
</footer>
