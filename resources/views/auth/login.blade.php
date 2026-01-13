<x-guest-layout>

    @if (session('status'))
        <div class="alert alert-success text-center mb-3 rounded-pill">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <input type="email" name="email" value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror" placeholder="Email address" required autofocus>
            @error('email')
                <div class="invalid-feedback d-block mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Password" required>
            @error('password')
                <div class="invalid-feedback d-block mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Remember + Forgot -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check text-white">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">
                    Remember me
                </label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small">
                    Forgot?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <button type="submit" class="btn btn-social w-100 mb-3">
            Log in
        </button>

        <!-- Divider -->
        <div class="divider">or</div>

        <!-- Social Login (UI only, optional backend later) -->
        <button type="button" class="btn btn-light w-100 mb-2 rounded-pill fw-semibold">
            Continue with Google
        </button>

        <button type="button" class="btn btn-dark w-100 rounded-pill fw-semibold">
            Continue with Apple
        </button>

        <!-- Register -->
        <div class="text-center mt-4">
            <small class="text-white-50">
                Don’t have an account?
                <a href="{{ route('register') }}" class="fw-semibold">
                    Sign up
                </a>
            </small>
        </div>

    </form>

</x-guest-layout>
