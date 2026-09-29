<x-layouts.app :categories="$categories" title="Login — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-auth">
        <div class="unyl-auth__card">
            <h1>Login</h1>

            @if (session('status'))
                <p class="unyl-contact__flash">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

            {{-- Google sign-in temporarily hidden — Bluehost's ModSecurity is blocking the
                 OAuth callback URL (false positive) pending a fix from their support.
                 Remove this comment wrapper once that's resolved to re-enable. --}}
            {{--
            <a href="{{ route('auth.google') }}" class="unyl-auth__google">
                <svg viewBox="0 0 18 18" width="18" height="18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.87 2.7-6.62z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.92v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.92A9 9 0 0 0 0 9c0 1.45.35 2.83.92 4.03l3.03-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .92 4.97L3.95 7.3C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
                Sign in with Google
            </a>

            <div class="unyl-auth__divider"><span>or</span></div>
            --}}

            <form method="POST" action="{{ route('login.attempt') }}" class="unyl-auth__form">
                @csrf
                <div class="unyl-field">
                    <label for="login">Username or email *</label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus />
                </div>
                <div class="unyl-field">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required />
                </div>
                <p class="unyl-auth__forgot"><a href="{{ route('password.request') }}">Forgot password?</a></p>
                <button type="submit" class="unyl-btn unyl-auth__submit">Log in</button>
            </form>

            <p class="unyl-auth__switch">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
        </div>
    </div>
</x-layouts.app>
