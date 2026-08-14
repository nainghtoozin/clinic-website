<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $staff->name ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $staff->email ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Password
            @isset($staff)
                <small class="text-muted">(leave blank to keep)</small>
            @else
                <span class="text-danger">*</span>
            @endisset
        </label>
        <input type="password" name="password" class="form-control" @unless(isset($staff)) required @endunless>
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Confirm Password
            @unless(isset($staff))
                <span class="text-danger">*</span>
            @endunless
        </label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $staff->phone ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Position</label>
        <input type="text" name="position" class="form-control" placeholder="e.g. Senior Nurse, Front Desk"
            value="{{ old('position', $staff->position ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Role <span class="text-danger">*</span></label>
        <select name="role" class="form-select" required>
            <option value="">-- Select Role --</option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}"
                    {{ old('role', $userRole ?? '') === $value ? 'selected' : '' }}>
                    {{ ucfirst($label) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Link to Doctor Account</label>
        <select name="doctor_id" class="form-select">
            <option value="">-- None --</option>
            @foreach ($doctors as $id => $name)
                <option value="{{ $id }}"
                    {{ old('doctor_id', $linkedDoctor ?? '') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Link this staff member to a doctor profile (for doctors only).</small>
    </div>

    @isset($staff)
        <div class="col-md-6">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-select" required>
                <option value="1" {{ old('is_active', $staff->is_active) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !old('is_active', $staff->is_active) ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    @endisset
</div>
