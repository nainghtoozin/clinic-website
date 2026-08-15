<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $staff->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $staff->email ?? '') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">
            Password
            @isset($staff)
                <small class="text-muted">(leave blank to keep)</small>
            @else
                <span class="text-danger">*</span>
            @endisset
        </label>
        <input type="password" id="password" name="password"
            class="form-control @error('password') is-invalid @enderror"
            @unless(isset($staff)) required @endunless>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">
            Confirm Password
            @unless(isset($staff))
                <span class="text-danger">*</span>
            @endunless
        </label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $staff->phone ?? '') }}">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="position" class="form-label">Position</label>
        <input type="text" id="position" name="position"
            class="form-control @error('position') is-invalid @enderror"
            placeholder="e.g. Senior Nurse, Front Desk" value="{{ old('position', $staff->position ?? '') }}">
        @error('position')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Roles are managed centrally via the Roles & Permissions module; only that workflow may change them. --}}
    <div class="col-md-6">
        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
            <option value="">-- Select Role --</option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}"
                    {{ old('role', $userRole ?? '') === $value ? 'selected' : '' }}>
                    {{ ucfirst($label) }}
                </option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="doctor_id" class="form-label">Link to Doctor Account</label>
        <select id="doctor_id" name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
            <option value="">-- None --</option>
            @foreach ($doctors as $id => $name)
                <option value="{{ $id }}"
                    {{ old('doctor_id', $linkedDoctor ?? '') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        @error('doctor_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Link this staff member to a doctor profile (for doctors only).</small>
    </div>

    @isset($staff)
        <div class="col-md-6">
            <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
            <select id="is_active" name="is_active" class="form-select @error('is_active') is-invalid @enderror"
                required>
                <option value="1" {{ old('is_active', $staff->is_active) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !old('is_active', $staff->is_active) ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endisset
</div>