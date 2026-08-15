<form method="post" action="{{ route('password.update') }}" autocomplete="off">
    @csrf
    @method('put')

    @if ($errors->updatePassword->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->updatePassword->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i> {{ __('app.security.password_updated') }}
        </div>
    @endif

    <div class="mb-3">
        <label for="current_password" class="form-label">{{ __('app.security.current_password') }}</label>
        <input id="current_password" type="password" name="current_password"
            class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
            autocomplete="current-password">
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="password" class="form-label">{{ __('app.security.new_password') }}</label>
            <input id="password" type="password" name="password"
                class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="password_confirmation" class="form-label">{{ __('app.security.confirm_password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                class="form-control" autocomplete="new-password">
        </div>
    </div>

    <button class="btn btn-primary">
        <i class="bi bi-key me-1"></i> {{ __('app.security.update_password') }}
    </button>
</form>
