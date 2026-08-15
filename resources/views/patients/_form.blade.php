@php
    $isEdit = isset($patient) && $patient !== null;
    $action = $isEdit ? route('patients.update', $patient) : route('patients.store');
@endphp

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

<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">
        {{-- Basic Information --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if ($isEdit)
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Patient Number</label>
                                <input type="text" class="form-control" value="{{ $patient->patient_number }}" disabled readonly>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $isEdit ? $patient->name : null) }}"
                                class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $isEdit ? $patient->phone : null) }}"
                                class="form-control @error('phone') is-invalid @enderror" maxlength="20">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $isEdit ? $patient->email : null) }}"
                                class="form-control @error('email') is-invalid @enderror" maxlength="255">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth"
                                value="{{ old('date_of_birth', $isEdit ? $patient->date_of_birth?->format('Y-m-d') : null) }}"
                                class="form-control @error('date_of_birth') is-invalid @enderror">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $isEdit ? $patient->gender : null) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $isEdit ? $patient->gender : null) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $isEdit ? $patient->gender : null) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                <option value="">Select Blood Group</option>
                                @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                                    <option value="{{ $group }}" {{ old('blood_group', $isEdit ? $patient->blood_group : null) === $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                @endforeach
                            </select>
                            @error('blood_group')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="2" maxlength="500"
                                class="form-control @error('address') is-invalid @enderror">{{ old('address', $isEdit ? $patient->address : null) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-tag me-2"></i>Status</h6>
                </div>
                <div class="card-body">
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', $isEdit ? $patient->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $isEdit ? $patient->status : null) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        @if ($isEdit)
                            <option value="archived" {{ old('status', $patient->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                        @endif
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        <i class="bi bi-info-circle me-1"></i>Inactive patients can be archived later.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-0">
        {{-- Emergency Contact --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-telephone me-2"></i>Emergency Contact</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $isEdit ? $patient->emergency_contact_name : null) }}"
                                class="form-control @error('emergency_contact_name') is-invalid @enderror" maxlength="255">
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $isEdit ? $patient->emergency_contact_phone : null) }}"
                                class="form-control @error('emergency_contact_phone') is-invalid @enderror" maxlength="20">
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Medical Information --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Medical Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Allergies</label>
                            <textarea name="allergies" rows="2" maxlength="1000" placeholder="List any known allergies"
                                class="form-control @error('allergies') is-invalid @enderror">{{ old('allergies', $isEdit ? $patient->allergies : null) }}</textarea>
                            @error('allergies')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Medical History</label>
                            <textarea name="medical_history" rows="3" maxlength="2000" placeholder="Relevant medical history"
                                class="form-control @error('medical_history') is-invalid @enderror">{{ old('medical_history', $isEdit ? $patient->medical_history : null) }}</textarea>
                            @error('medical_history')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ $isEdit ? 'Update Patient' : 'Register Patient' }}
        </button>
    </div>
</form>