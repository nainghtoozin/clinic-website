@csrf
@if (isset($doctor))
    @method('PUT')
@endif

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0 fw-bold">{{ $formTitle }}</h5>
    </div>

    <div class="card-body">
        {{-- BASIC INFO --}}
        <h6 class="text-primary mb-3">Basic Information</h6>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Doctor Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $doctor->name ?? '') }}"
                    class="form-control @error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="gender" class="form-label">Gender</label>
                <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                    <option value="">-- Select --</option>
                    @foreach (['male', 'female', 'other'] as $g)
                        <option value="{{ $g }}" @selected(old('gender', $doctor->gender ?? '') == $g)>
                            {{ ucfirst($g) }}
                        </option>
                    @endforeach
                </select>
                @error('gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="profile_image" class="form-label">Profile Image</label>
                @if (!empty($doctor->profile_image))
                    <div class="mb-2">
                        <img src="{{ Storage::url($doctor->profile_image) }}" class="rounded"
                            style="width:64px;height:64px;object-fit:cover;" alt="Current photo">
                        <small class="text-muted d-block">Current photo</small>
                    </div>
                @endif
                <input type="file" id="profile_image" accept="image/*" name="profile_image"
                    class="form-control @error('profile_image') is-invalid @enderror">
                <small class="text-muted d-block mt-1">JPG, PNG or WEBP &middot; max 2MB</small>
                @error('profile_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- PROFESSIONAL INFO --}}
        <hr class="my-4">
        <h6 class="text-primary mb-3">Professional Information</h6>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="title" class="form-label">Title</label>
                <input id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $doctor->title ?? '') }}" placeholder="Cardiologist • MD, FACC">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="role" class="form-label">Role</label>
                <input id="role" name="role" class="form-control @error('role') is-invalid @enderror"
                    value="{{ old('role', $doctor->role ?? '') }}" placeholder="Senior Consultant">
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="qualifications" class="form-label">Qualifications</label>
                <input id="qualifications" name="qualifications"
                    class="form-control @error('qualifications') is-invalid @enderror"
                    value="{{ old('qualifications', $doctor->qualifications ?? '') }}" placeholder="MBBS, MD">
                @error('qualifications')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="experience_years" class="form-label">Experience (Years)</label>
                <input id="experience_years" type="number" min="0" name="experience_years"
                    value="{{ old('experience_years', $doctor->experience_years ?? 0) }}"
                    class="form-control @error('experience_years') is-invalid @enderror">
                @error('experience_years')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="consultation_fee" class="form-label">Consultation Fee</label>
                <input id="consultation_fee" type="number" min="0" step="0.01" name="consultation_fee"
                    value="{{ old('consultation_fee', $doctor->consultation_fee ?? '') }}"
                    class="form-control @error('consultation_fee') is-invalid @enderror" placeholder="0.00">
                @error('consultation_fee')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="board_certified" class="form-label">Board Certified</label>
                <select id="board_certified" name="board_certified"
                    class="form-select @error('board_certified') is-invalid @enderror">
                    <option value="0" @selected(old('board_certified', $doctor->board_certified ?? 0) == 0)>No</option>
                    <option value="1" @selected(old('board_certified', $doctor->board_certified ?? 0) == 1)>Yes</option>
                </select>
                @error('board_certified')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="department_id" class="form-label">Department</label>
                <select id="department_id" name="department_id"
                    class="form-select @error('department_id') is-invalid @enderror">
                    <option value="">-- Select --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id', $doctor->department_id ?? '') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- DESCRIPTION --}}
        <hr class="my-4">
        <h6 class="text-primary mb-3">Description</h6>

        <div class="mb-3">
            <label for="short_description" class="form-label">Short Description</label>
            <textarea id="short_description" name="short_description" rows="2"
                class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $doctor->short_description ?? '') }}</textarea>
            @error('short_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="biography" class="form-label">Biography</label>
            <textarea id="biography" name="biography" rows="4"
                class="form-control @error('biography') is-invalid @enderror">{{ old('biography', $doctor->biography ?? '') }}</textarea>
            @error('biography')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- STATUS --}}
        <hr class="my-4">
        <h6 class="text-primary mb-3">Status</h6>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="is_available" class="form-label">Available</label>
                <select id="is_available" name="is_available"
                    class="form-select @error('is_available') is-invalid @enderror">
                    <option value="1" @selected(old('is_available', $doctor->is_available ?? 1) == 1)>Yes</option>
                    <option value="0" @selected(old('is_available', $doctor->is_available ?? 1) == 0)>No</option>
                </select>
                @error('is_available')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="availability_note" class="form-label">Availability Note</label>
                <input id="availability_note" name="availability_note"
                    class="form-control @error('availability_note') is-invalid @enderror"
                    value="{{ old('availability_note', $doctor->availability_note ?? '') }}">
                @error('availability_note')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="is_featured" class="form-label">Featured Doctor</label>
                <select id="is_featured" name="is_featured"
                    class="form-select @error('is_featured') is-invalid @enderror">
                    <option value="0" @selected(old('is_featured', $doctor->is_featured ?? 0) == 0)>No</option>
                    <option value="1" @selected(old('is_featured', $doctor->is_featured ?? 0) == 1)>Yes</option>
                </select>
                @error('is_featured')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="card border-primary shadow-sm mx-3 mb-3">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="bi bi-calendar-week me-2"></i>
                Doctor Availability Schedule
            </h6>
        </div>

        <div class="card-body">
            {{-- Available Days --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Available Days <span class="text-danger">*</span>
                </label>

                <div class="d-flex flex-wrap gap-3 @error('days') is-invalid d-block @enderror">
                    @foreach ($days as $value => $label)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="days[]" id="day_{{ $value }}"
                                value="{{ $value }}" @checked(in_array($value, old('days', $doctor->available_days ?? [])))>
                            <label class="form-check-label" for="day_{{ $value }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
                @error('days')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Time --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="start_time" class="form-label fw-semibold">
                        Start Time <span class="text-danger">*</span>
                    </label>
                    <input type="time" id="start_time" name="start_time"
                        class="form-control @error('start_time') is-invalid @enderror"
                        value="{{ old('start_time', $doctor->start_time ?? '') }}" required>
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="end_time" class="form-label fw-semibold">
                        End Time <span class="text-danger">*</span>
                    </label>
                    <input type="time" id="end_time" name="end_time"
                        class="form-control @error('end_time') is-invalid @enderror"
                        value="{{ old('end_time', $doctor->end_time ?? '') }}" required>
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <small class="text-muted d-block mt-3">
                <i class="bi bi-info-circle me-1"></i>
                This schedule will be shown publicly on the doctor profile.
            </small>
        </div>
    </div>

    <div class="card-footer bg-white text-end">
        <a href="{{ route('doctors.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary">
            {{ isset($doctor) ? 'Update Doctor' : 'Create Doctor' }}
        </button>
    </div>
</div>