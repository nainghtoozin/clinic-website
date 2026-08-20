<x-guest-layout>

    <div class="text-center mb-4">
        <h2 class="auth-card-title">Reset your password</h2>
        <p class="auth-card-sub mb-0">Choose a new password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="you@clinic.com" required autofocus autocomplete="username">
            </div>
            @error('email')
                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3" x-data="{ show: false }">
            <label for="password" class="form-label">New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input id="password" :type="show ? 'text' : 'password'" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter a new password" required autocomplete="new-password">
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

        <!-- Confirm Password -->
        <div class="mb-4" x-data="{ show: false }">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation"
                    class="form-control"
                    placeholder="Re-enter your password" required autocomplete="new-password">
                <button type="button" class="input-group-text auth-input-toggle" @click="show = !show"
                    tabindex="-1" :aria-label="show ? 'Hide password' : 'Show password'"
                    :title="show ? 'Hide password' : 'Show password'">
                    <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary auth-submit w-100 py-2 fw-semibold">
            <i class="bi bi-check2-circle me-2"></i>Reset Password
        </button>
    </form>

</x-guest-layout>
