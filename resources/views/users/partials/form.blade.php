<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Password
            @isset($user)
                <small class="text-muted">(leave blank to keep)</small>
            @endisset
        </label>
        <input type="password" name="password" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    @php
        $currentRole = old('role', isset($user) ? $user->roles->first()->name ?? '' : '');
    @endphp

    {{-- Role --}}
    <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
            <option value="">-- Select Role --</option>

            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" {{ $currentRole === $value ? 'selected' : '' }}>
                    {{ ucfirst($label) }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Status --}}
    <div class="col-md-6 d-flex align-items-center mt-4">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active"
                {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
    </div>
</div>
