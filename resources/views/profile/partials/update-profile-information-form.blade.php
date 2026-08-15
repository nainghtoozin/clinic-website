<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('patch')

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-2" role="alert">
            <i class="bi bi-exclamation-octagon fs-5 mt-1"></i>
            <div>
                <strong class="d-block mb-1">Please fix the following errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success border-0 shadow-sm py-2">
            <i class="bi bi-check-circle me-1"></i> {{ __('app.settings.profile_updated') }}
        </div>
    @endif

    {{-- Profile photo --}}
    <div class="d-flex align-items-center gap-4 mb-4">
        <div class="position-relative">
            @if ($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="profile-avatar">
            @else
                <span class="profile-avatar">{{ initials($user->name) }}</span>
            @endif
            <label for="avatar" class="avatar-upload-btn" title="Change profile photo">
                <i class="bi bi-camera-fill" style="font-size:0.8rem;"></i>
            </label>
            <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/gif,image/webp"
                class="d-none @error('avatar') is-invalid @enderror">
        </div>
        <div class="min-w-0">
            <div class="fw-semibold fs-6">{{ $user->name }}</div>
            <div class="text-muted small">{{ $user->email }}</div>
            <small class="text-muted d-block mt-1">JPG, PNG, GIF or WEBP &middot; max 2MB</small>
        </div>
        @error('avatar')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="profile_name" class="form-label">{{ __('app.settings.name') }} <span class="text-danger">*</span></label>
            <input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}"
                class="form-control @error('name') is-invalid @enderror" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="profile_email" class="form-label">{{ __('app.settings.email') }} <span class="text-danger">*</span></label>
            <input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}"
                class="form-control @error('email') is-invalid @enderror" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="profile_phone" class="form-label">{{ __('app.settings.phone') }}</label>
            <input id="profile_phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                class="form-control @error('phone') is-invalid @enderror">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check2 me-1"></i> {{ __('app.settings.save_changes') }}
        </button>
    </div>
</form>
