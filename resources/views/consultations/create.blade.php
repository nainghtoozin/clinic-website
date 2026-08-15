<x-auth-layout>
    <x-page-header title="New Consultation" subtitle="{{ $ticket ? 'Queue Ticket: ' . $ticket->ticket_number : 'Start a clinical consultation' }}"
        :breadcrumbs="[['label' => 'Consultations', 'url' => route('consultations.index')], ['label' => 'New Consultation']]">
        <a href="{{ $ticket ? route('queue.index') : route('consultations.index') }}"
            class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
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

    {{-- Clinical workflow steps --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2 small align-items-center">
                <span class="fw-semibold text-muted"><i class="bi bi-diagram-3 me-1"></i>Workflow:</span>
                @foreach (['Consultation', 'Vital Signs', 'Symptoms', 'Diagnosis', 'Treatment', 'Follow-up'] as $i => $step)
                    <span class="badge {{ $i === 0 ? 'bg-primary' : 'bg-light text-dark border' }}">{{ $step }}</span>
                    @if (!$loop->last)
                        <i class="bi bi-chevron-right text-muted"></i>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('consultations.store') }}" novalidate>
        @csrf

        @if ($ticket)
            <input type="hidden" name="queue_ticket_id" value="{{ $ticket->id }}">
        @endif
        @if ($appointment)
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Patient Information --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient Information</h6>
                    </div>
                    <div class="card-body">
                        @if ($patient)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="avatar bg-primary" style="width:48px;height:48px;font-size:1rem;">{{ initials($patient->name) }}</span>
                                <div class="min-w-0">
                                    <div class="fw-semibold">{{ $patient->name }}</div>
                                    <small class="text-muted">{{ $patient->patient_number }}</small>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Age / Gender</label>
                                    <div class="fw-semibold">{{ $patient->date_of_birth ? $patient->date_of_birth->age . ' yrs' : '-' }} / {{ ucfirst($patient->gender ?? '-') }}</div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Phone</label>
                                    <div class="fw-semibold">{{ $patient->phone ?? '-' }}</div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Blood Group</label>
                                    <div class="fw-semibold">{{ $patient->blood_group ?? '-' }}</div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Allergies</label>
                                    @if ($patient->allergies)
                                        <div class="text-danger fw-semibold">
                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $patient->allergies }}
                                        </div>
                                    @else
                                        <div class="text-success">None recorded</div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label text-muted small mb-0">Medical History</label>
                                    <div>{{ Str::limit($patient->medical_history ?? 'None', 80) }}</div>
                                </div>
                            </div>
                            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                        @else
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">Select Patient</option>
                                @foreach ($patients as $p)
                                    <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->patient_number }} - {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                {{-- Visit Information --}}
                @if ($appointment)
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Visit Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Appointment</label>
                                    <div><span class="badge bg-primary">{{ $appointment->appointment_number }}</span></div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Date</label>
                                    <div class="fw-semibold">{{ fmt_date($appointment->date) }} &middot; {{ $appointment->time ? fmt_time($appointment->time) : '' }}</div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Doctor</label>
                                    <div class="fw-semibold">{{ $ticket?->doctor?->name ?? $appointment->doctor?->name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Clinical Information --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Clinical Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Symptoms / Chief Complaint</label>
                            <textarea name="symptoms" rows="3" class="form-control @error('symptoms') is-invalid @enderror"
                                placeholder="Patient's main complaint...">{{ old('symptoms') }}</textarea>
                            @error('symptoms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" rows="2" class="form-control @error('diagnosis') is-invalid @enderror"
                                placeholder="Diagnosis...">{{ old('diagnosis') }}</textarea>
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Clinical Notes</label>
                            <textarea name="clinical_notes" rows="3" class="form-control @error('clinical_notes') is-invalid @enderror"
                                placeholder="Detailed clinical notes...">{{ old('clinical_notes') }}</textarea>
                            @error('clinical_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Treatment Plan</label>
                            <textarea name="treatment_plan" rows="3" class="form-control @error('treatment_plan') is-invalid @enderror"
                                placeholder="Treatment plan...">{{ old('treatment_plan') }}</textarea>
                            @error('treatment_plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Doctor --}}
                @if (!$ticket)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Doctor</h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                <option value="">Select Doctor</option>
                                @foreach ($doctors as $doc)
                                    <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->name }} {{ $doc->department ? '(' . $doc->department->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @else
                    <input type="hidden" name="doctor_id" value="{{ $ticket->doctor_id }}">
                @endif

                {{-- Vital Signs --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-thermometer-half me-2"></i>Vital Signs</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Blood Pressure</label>
                            <input type="text" name="blood_pressure" class="form-control @error('blood_pressure') is-invalid @enderror"
                                placeholder="e.g. 120/80" value="{{ old('blood_pressure') }}">
                            @error('blood_pressure')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Temp (°C)</label>
                                <input type="number" name="temperature" class="form-control @error('temperature') is-invalid @enderror" step="0.1"
                                    min="30" max="45" value="{{ old('temperature') }}">
                                @error('temperature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">Pulse (bpm)</label>
                                <input type="number" name="pulse" class="form-control @error('pulse') is-invalid @enderror"
                                    min="30" max="250" value="{{ old('pulse') }}">
                                @error('pulse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Resp. Rate</label>
                                <input type="number" name="respiratory_rate" class="form-control @error('respiratory_rate') is-invalid @enderror"
                                    min="5" max="60" value="{{ old('respiratory_rate') }}">
                                @error('respiratory_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">SpO2 (%)</label>
                                <input type="number" name="oxygen_saturation" class="form-control @error('oxygen_saturation') is-invalid @enderror" step="0.1"
                                    min="0" max="100" value="{{ old('oxygen_saturation') }}">
                                @error('oxygen_saturation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" name="weight" class="form-control @error('weight') is-invalid @enderror" step="0.1"
                                    min="0" max="500" value="{{ old('weight') }}">
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" name="height" class="form-control @error('height') is-invalid @enderror" step="0.1"
                                    min="0" max="300" value="{{ old('height') }}">
                                @error('height')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Follow-up --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-calendar-date me-2"></i>Follow-up</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror"
                                value="{{ old('follow_up_date') }}" min="{{ now()->addDay()->format('Y-m-d') }}">
                            @error('follow_up_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Follow-up Notes</label>
                            <textarea name="follow_up_notes" class="form-control @error('follow_up_notes') is-invalid @enderror" rows="2"
                                placeholder="Follow-up instructions...">{{ old('follow_up_notes') }}</textarea>
                            @error('follow_up_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ $ticket ? route('queue.index') : route('consultations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Save Consultation
            </button>
        </div>
    </form>
</x-auth-layout>
