<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('patch')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-3">
        <label class="form-label">{{ __('app.settings.profile_photo') }}</label>
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
                alt="{{ $user->name }}" class="rounded-circle" width="100" height="100" style="object-fit:cover">
            <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp"
                class="form-control @error('avatar') is-invalid @enderror">
        </div>
        @error('avatar')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">{{ __('app.settings.name') }}</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">{{ __('app.settings.email') }}</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">{{ __('app.settings.phone') }}</label>
        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
    </div>

    <button class="btn btn-primary">
        {{ __('app.settings.save_changes') }}
    </button>

    @if (session('status') === 'profile-updated')
        <span class="text-success ms-3">{{ __('app.settings.profile_updated') }}</span>
    @endif
</form>