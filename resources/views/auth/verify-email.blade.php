<x-guest-layout>

    <div class="text-center mb-4">
        <h2 class="auth-card-title">Verify your email</h2>
        <p class="auth-card-sub mb-0">Thanks for signing up! Before getting started, could you verify your email address by clicking the link we just emailed you? If you didn't receive the email, we will gladly send you another.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success py-2 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle me-2"></i>A new verification link has been sent to the email address you provided during registration.
        </div>
    @endif

    <div class="d-grid gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary auth-submit w-100 py-2 fw-semibold">
                <i class="bi bi-envelope-arrow-up me-2"></i>Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100 py-2 fw-medium">
                <i class="bi bi-box-arrow-left me-2"></i>Log Out
            </button>
        </form>
    </div>

</x-guest-layout>
