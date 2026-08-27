<x-auth-layout>
    <x-page-header title="New Investigation" subtitle="Request a lab investigation for a patient"
        :breadcrumbs="[['label' => 'Investigations', 'url' => route('investigations.index')], ['label' => 'New Investigation']]">
        <a href="{{ route('investigations.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
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

    <form method="POST" action="{{ route('investigations.store') }}" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-clipboard2-data me-2"></i>Investigation Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Patient <span class="text-danger">*</span></label>
                                @if ($patient)
                                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                                    <div class="form-control bg-light">{{ $patient->name }} ({{ $patient->patient_number }})</div>
                                @else
                                    <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                        <option value="">Select Patient</option>
                                        @foreach ($patients as $p)
                                            <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->patient_number }})</option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                @if ($consultation)
                                    <input type="hidden" name="doctor_id" value="{{ $consultation->doctor_id }}">
                                    <div class="form-control bg-light">Dr. {{ $consultation->doctor->name }}</div>
                                @else
                                    <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                        <option value="">Select Doctor</option>
                                        @foreach ($doctors as $doc)
                                            <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>Dr. {{ $doc->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if ($consultation)
                                <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Lab Test <span class="text-danger">*</span></label>
                                <select name="lab_test_id" class="form-select @error('lab_test_id') is-invalid @enderror" required>
                                    <option value="">Select Test</option>
                                    @foreach ($labTests as $test)
                                        <option value="{{ $test->id }}" data-price="{{ $test->price }}" {{ old('lab_test_id') == $test->id ? 'selected' : '' }}>
                                            {{ $test->name }} ({{ $test->code }}) - ${{ number_format($test->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lab_test_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Requested Date <span class="text-danger">*</span></label>
                                <input type="date" name="requested_date" class="form-control @error('requested_date') is-invalid @enderror" value="{{ old('requested_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required>
                                @error('requested_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="routine" {{ old('priority') === 'routine' ? 'selected' : '' }}>Routine</option>
                                    <option value="stat" {{ old('priority') === 'stat' ? 'selected' : '' }}>STAT</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Clinical Notes</label>
                                <textarea name="clinical_notes" class="form-control @error('clinical_notes') is-invalid @enderror" rows="3" placeholder="Any relevant clinical information...">{{ old('clinical_notes') }}</textarea>
                                @error('clinical_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @if ($consultation)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Consultation</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <label class="form-label text-muted small mb-0">Consultation #</label>
                                <div>{{ $consultation->id }}</div>
                            </div>
                            @if ($consultation->diagnosis)
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-0">Diagnosis</label>
                                    <div class="small">{{ $consultation->diagnosis }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Instructions</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Select the patient and lab test</li>
                            <li>Choose priority level</li>
                            <li>Add clinical notes if needed</li>
                            <li>The investigation will appear as "Requested"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('investigations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Create Investigation
            </button>
        </div>
    </form>
</x-auth-layout>
