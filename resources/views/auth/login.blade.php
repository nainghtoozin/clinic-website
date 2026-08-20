<x-guest-layout>

    <div class="text-center mb-4">
        <h2 class="auth-card-title">Welcome back</h2>
        <p class="auth-card-sub mb-0">Sign in to your clinic workspace</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="you@clinic.com" required autofocus autocomplete="username">
            </div>
            @error('email')
                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3" x-data="{ show: false }">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input id="password" :type="show ? 'text' : 'password'" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter your password" required autocomplete="current-password">
                <button type="button" class="input-group-text auth-input-toggle" @click="show = !show"
                    tabindex="-1" :aria-label="show ? 'Hide password' : 'Show password'"
                    :title="show ? 'Hide password' : 'Show password'">
                    <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember + Forgot -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">
                    Remember me
                </label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small fw-medium">Forgot password?</a>
            @endif
        </div>

        <!-- Sign In -->
        <button type="submit" class="btn btn-primary auth-submit w-100 py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </button>
    </form>

</x-guest-layout>
