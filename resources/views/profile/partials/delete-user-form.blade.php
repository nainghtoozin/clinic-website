<form method="post" action="{{ route('profile.destroy') }}">
    @csrf
    @method('delete')

    @if ($errors->userDeletion->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->userDeletion->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-muted">
        {{ __('app.account.warning') }}
    </p>

    <div class="mb-3">
        <label class="form-label text-danger">{{ __('app.account.current_password') }}</label>
        <input type="password" name="password"
            class="form-control @error('password', 'userDeletion') is-invalid @enderror" autocomplete="current-password">
        @error('password', 'userDeletion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label text-danger">{{ __('app.account.confirm_email') }}</label>
        <input type="email" name="confirm_email" value="{{ old('confirm_email') }}"
            class="form-control @error('confirm_email', 'userDeletion') is-invalid @enderror" autocomplete="off">
        <div class="form-text">{{ __('app.account.confirm_email_help') }}</div>
        @error('confirm_email', 'userDeletion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button class="btn btn-danger">
        {{ __('app.account.submit') }}
    </button>
</form>
