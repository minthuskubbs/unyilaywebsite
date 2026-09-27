<x-layouts.app :categories="$categories" title="Register — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-auth">
        <div class="unyl-auth__card">
            <h1>Register</h1>

            @if ($errors->any())
                <p class="unyl-contact__flash unyl-contact__flash--error">{{ $errors->first() }}</p>
            @endif

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
