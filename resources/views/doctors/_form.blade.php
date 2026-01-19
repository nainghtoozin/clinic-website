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
                <label class="form-label">Doctor Name *</label>
                <input type="text" name="name" value="{{ old('name', $doctor->name ?? '') }}" class="form-control"
                    required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach (['male', 'female', 'other'] as $g)
                        <option value="{{ $g }}" @selected(old('gender', $doctor->gender ?? '') == $g)>
                            {{ ucfirst($g) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                {{-- <img src="{{ Storage::link($doctor->profile_image) }}" alt="No Photo" width="100px" height="100px"> --}}
                <label class="form-label">Profile Image (URL)</label>
                <input type="file" name="profile_image" class="form-control"
                    value="{{ old('profile_image', $doctor->profile_image ?? '') }}">

            </div>
        </div>

        {{-- PROFESSIONAL INFO --}}
        <hr class="my-4">
        <h6 class="text-primary mb-3">Professional Information</h6>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" value="{{ old('title', $doctor->title ?? '') }}"
                    placeholder="Cardiologist • MD, FACC">
            </div>

            <div class="col-md-4">
                <label class="form-label">Role</label>
                <input name="role" class="form-control" value="{{ old('role', $doctor->role ?? '') }}"
                    placeholder="Senior Consultant">
            </div>

            <div class="col-md-4">
                <label class="form-label">Qualifications</label>
                <input name="qualifications" class="form-control"
                    value="{{ old('qualifications', $doctor->qualifications ?? '') }}" placeholder="MBBS, MD">
            </div>

            <div class="col-md-3">
                <label class="form-label">Experience (Years)</label>
                <input type="number" min="0" name="experience_years"
                    value="{{ old('experience_years', $doctor->experience_years ?? 0) }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Board Certified</label>
                <select name="board_certified" class="form-select">
                    <option value="0" @selected(old('board_certified', $doctor->board_certified ?? 0) == 0)>No</option>
                    <option value="1" @selected(old('board_certified', $doctor->board_certified ?? 0) == 1)>Yes</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id', $doctor->department_id ?? '') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Primary Department (Text)</label>
                <input name="primary_department" class="form-control"
                    value="{{ old('primary_department', $doctor->primary_department ?? '') }}">
            </div>
        </div>

        {{-- DESCRIPTION --}}
        <hr class="my-4">
        <h6 class="text-primary mb-3">Description</h6>

        <div class="mb-3">
            <label class="form-label">Short Description</label>
            <textarea name="short_description" rows="2" class="form-control">{{ old('short_description', $doctor->short_description ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Biography</label>
            <textarea name="biography" rows="4" class="form-control">{{ old('biography', $doctor->biography ?? '') }}</textarea>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Location</label>
                <input name="location" class="form-control" value="{{ old('location', $doctor->location ?? '') }}">
            </div>
        </div>

        {{-- STATUS --}}
        <hr class="my-4">
        <h6 class="text-primary mb-3">Status</h6>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Available</label>
                <select name="is_available" class="form-select">
                    <option value="1" @selected(old('is_available', $doctor->is_available ?? 1) == 1)>Yes</option>
                    <option value="0" @selected(old('is_available', $doctor->is_available ?? 1) == 0)>No</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Availability Note</label>
                <input name="availability_note" class="form-control"
                    value="{{ old('availability_note', $doctor->availability_note ?? '') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Featured Doctor</label>
                <select name="is_featured" class="form-select">
                    <option value="0" @selected(old('is_featured', $doctor->is_featured ?? 0) == 0)>No</option>
                    <option value="1" @selected(old('is_featured', $doctor->is_featured ?? 0) == 1)>Yes</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white text-end">
        <a href="{{ route('doctors.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary">
            {{ isset($doctor) ? 'Update Doctor' : 'Create Doctor' }}
        </button>
    </div>
</div>
