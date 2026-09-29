<x-layouts.app :categories="$categories" title="Reset Password — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-auth">
        <div class="unyl-auth__card">
            <h1>Reset Password</h1>

            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="unyl-auth__form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />
                <div class="unyl-field">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus />
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

            <p class="unyl-auth__switch">Remembered your password? <a href="{{ route('login') }}">Log in</a></p>
        </div>
    </div>
</x-layouts.app>
