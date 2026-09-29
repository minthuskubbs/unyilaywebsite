<x-layouts.app :categories="$categories" title="Register — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-auth">
        <div class="unyl-auth__card">
            <h1>Register</h1>

            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

            <a href="{{ route('auth.google') }}" class="unyl-auth__google">
                <svg viewBox="0 0 18 18" width="18" height="18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.87 2.7-6.62z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.92v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.92A9 9 0 0 0 0 9c0 1.45.35 2.83.92 4.03l3.03-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .92 4.97L3.95 7.3C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
                Sign up with Google
            </a>

            <div class="unyl-auth__divider"><span>or</span></div>

            <form method="POST" action="{{ route('register.attempt') }}" class="unyl-auth__form">
                @csrf
                <div class="unyl-field">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus />
                </div>
                <div class="unyl-field">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required />
                </div>
                <div class="unyl-field">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="8" />
                </div>
                <div class="unyl-field">
                    <label for="password_confirmation">Confirm password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" />
                </div>
                <button type="submit" class="unyl-btn unyl-auth__submit">Register</button>
            </form>

            <p class="unyl-auth__switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
        </div>
    </div>
</x-layouts.app>
