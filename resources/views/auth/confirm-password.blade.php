<x-guest-layout>

    <div class="text-center mb-4">
        <h2 class="auth-card-title">Confirm your password</h2>
        <p class="auth-card-sub mb-0">This is a secure area of the application. Please confirm your password before continuing.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" novalidate>
        @csrf

        <!-- Password -->
        <div class="mb-4" x-data="{ show: false }">
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

        <!-- Submit -->
        <button type="submit" class="btn btn-primary auth-submit w-100 py-2 fw-semibold">
            <i class="bi bi-shield-lock me-2"></i>Confirm
        </button>
    </form>

</x-guest-layout>
