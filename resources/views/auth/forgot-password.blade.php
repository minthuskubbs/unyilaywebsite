<x-layouts.app :categories="$categories" title="Forgot Password — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-auth">
        <div class="unyl-auth__card">
            <h1>Forgot Password</h1>

            @if (session('status'))
                <p class="unyl-contact__flash">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

            <p class="unyl-auth__hint">Enter the email address on your account and we'll send you a link to reset your password.</p>

            <form method="POST" action="{{ route('password.email') }}" class="unyl-auth__form">
                @csrf
                <div class="unyl-field">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus />
                </div>
                <button type="submit" class="unyl-btn unyl-auth__submit">Send Reset Link</button>
            </form>

            <p class="unyl-auth__switch">Remembered your password? <a href="{{ route('login') }}">Log in</a></p>
        </div>
    </div>
</x-layouts.app>
