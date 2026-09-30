<x-layouts.app :categories="$categories" title="Reset Password — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-auth">
        <div class="unyl-auth__card">
            <h1>Reset Password</h1>

            @if (session('status'))
                <p class="unyl-contact__flash">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

            <p class="unyl-auth__hint">Enter the 6-digit code we emailed you along with your new password.</p>

            <form method="POST" action="{{ route('password.update') }}" class="unyl-auth__form">
                @csrf
                <div class="unyl-field">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus />
                </div>
                <div class="unyl-field">
                    <label for="code">Reset code *</label>
                    <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="123456" required />
                </div>
                <div class="unyl-field">
                    <label for="password">New password *</label>
                    <input type="password" id="password" name="password" required minlength="8" />
                </div>
                <div class="unyl-field">
                    <label for="password_confirmation">Confirm new password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" />
                </div>
                <button type="submit" class="unyl-btn unyl-auth__submit">Reset Password</button>
            </form>

            <p class="unyl-auth__switch">Didn't get a code? <a href="{{ route('password.request') }}">Request a new one</a></p>
            <p class="unyl-auth__switch">Remembered your password? <a href="{{ route('login') }}">Log in</a></p>
        </div>
    </div>
</x-layouts.app>
