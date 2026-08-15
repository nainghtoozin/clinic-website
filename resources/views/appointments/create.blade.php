<x-auth-layout>
    <x-page-header title="New Appointment" subtitle="Schedule an appointment for a patient"
        :breadcrumbs="[['label' => 'Appointments', 'url' => route('appointments.index')], ['label' => 'New Appointment']]">
        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-page-header>

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

    <form method="POST" action="{{ route('appointments.store') }}" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Appointment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" id="patient-select" required>
                                    <option value="">Select Patient</option>
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}"
                                            {{ old('patient_id', $selectedPatient) == $patient->id ? 'selected' : '' }}
                                            data-name="{{ $patient->name }}" data-email="{{ $patient->email }}"
                                            data-phone="{{ $patient->phone }}">
                                            {{ $patient->patient_number }} - {{ $patient->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" id="department-select" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" id="doctor-select" required>
                                    <option value="">Select Doctor</option>
                                    @foreach ($doctors as $doc)
                                        <option value="{{ $doc->id }}"
                                            {{ old('doctor_id', $selectedDoctor) == $doc->id ? 'selected' : '' }}
                                            data-department="{{ $doc->department_id ?? '' }}"
                                            data-days="{{ json_encode($doc->available_days) }}"
                                            data-start="{{ $doc->start_time }}" data-end="{{ $doc->end_time }}"
                                            data-fee="{{ $doc->consultation_fee }}">
                                            {{ $doc->name }} {{ $doc->department ? '(' . $doc->department->name . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" id="date-input"
                                    value="{{ old('date') }}" min="{{ now()->format('Y-m-d') }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Duration <span class="text-danger">*</span></label>
                                <select name="duration" class="form-select @error('duration') is-invalid @enderror" id="duration-select" required>
                                    @foreach ([15, 30, 45, 60, 90, 120] as $d)
                                        <option value="{{ $d }}" {{ old('duration', 30) == $d ? 'selected' : '' }}>
                                            {{ $d }} minutes
                                        </option>
                                    @endforeach
                                </select>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="3"
                                    placeholder="Reason for visit or special instructions">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Schedule Appointment
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                @include('appointments.partials.schedule-picker')
            </div>
        </div>
    </form>
</x-auth-layout>
