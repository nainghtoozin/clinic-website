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
        <img src="{{ Storage::url($user->avatar) }}" alt="no profile image" class="rounded-circle mb-3" width="100"
            height="100">
        <label class="form-label">Avatar</label>
        <input type="file" name="avatar" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">UserName</label>
        <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Bio</label>
        <textarea name="bio" class="form-control" rows="3">{{ old('bio', $user->bio) }}</textarea>
    </div>

    {{-- <div class="mb-3">
        <label class="form-label">IS Active</label>
        <input type="checkbox" name="status" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
            class="form-check-input">
    </div> --}}

    <button class="btn btn-primary">
        Save Changes
    </button>

    @if (session('status') === 'profile-updated')
        <span class="text-success ms-3">Saved.</span>
    @endif
</form>
