<x-layouts.app :categories="$categories" title="Login — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-auth">
        <div class="unyl-auth__card">
            <h1>Login</h1>

            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

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
                <button type="submit" class="unyl-btn unyl-auth__submit">Log in</button>
            </form>
        </div>
    </div>
</x-layouts.app>
