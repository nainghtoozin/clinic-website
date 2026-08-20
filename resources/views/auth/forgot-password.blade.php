<x-guest-layout>

    <div class="text-center mb-4">
        <h2 class="auth-card-title">Forgot your password?</h2>
        <p class="auth-card-sub mb-0">No problem. Enter your email and we will email you a password reset link.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
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

        <!-- Submit -->
        <button type="submit" class="btn btn-primary auth-submit w-100 py-2 fw-semibold">
            <i class="bi bi-envelope-arrow-up me-2"></i>Email Password Reset Link
        </button>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="small fw-medium auth-back-link">
                <i class="bi bi-arrow-left me-1"></i>Back to sign in
            </a>
        </div>
    </form>

</x-guest-layout>
