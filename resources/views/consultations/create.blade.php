<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">New Consultation</h5>
            @if ($ticket)
                <small class="text-muted">Queue Ticket: {{ $ticket->ticket_number }}</small>
            @endif
        </div>
        <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Queue
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('consultations.store') }}">
        @csrf

        @if ($ticket)
            <input type="hidden" name="queue_ticket_id" value="{{ $ticket->id }}">
        @endif
        @if ($appointment)
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        @endif
        <input type="hidden" name="doctor_id" value="{{ $ticket ? $ticket->doctor_id : '' }}">

        <div class="row">
            <div class="col-lg-8">
                {{-- Patient Information --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient Information</h6>
                    </div>
                    <div class="card-body">
                        @if ($patient)
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Patient #</label>
                                    <div class="fw-semibold">{{ $patient->patient_number }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Name</label>
                                    <div class="fw-semibold">{{ $patient->name }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Age/Gender</label>
                                    <div>{{ $patient->date_of_birth ? $patient->date_of_birth->age . ' yrs' : '-' }} / {{ ucfirst($patient->gender ?? '-') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Phone</label>
                                    <div>{{ $patient->phone ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Allergies</label>
                                    @if ($patient->allergies)
                                        <div class="text-danger fw-semibold">
                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $patient->allergies }}
                                        </div>
                                    @else
                                        <div class="text-success">None recorded</div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Medical History</label>
                                    <div>{{ Str::limit($patient->medical_history ?? 'None', 50) }}</div>
                                </div>
                            </div>
                            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                        @else
                            <div class="mb-3">
                                <label class="form-label">Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select Patient</option>
                                    @foreach ($patients as $p)
                                        <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->patient_number }} - {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Visit Information --}}
                @if ($appointment)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Visit Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Appointment #</label>
                                    <div><span class="badge bg-primary">{{ $appointment->appointment_number }}</span></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Date</label>
                                    <div>{{ fmt_date($appointment->date) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Doctor</label>
                                    <div>{{ $ticket->doctor->name }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Clinical Information --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Clinical Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Chief Complaint / Symptoms <span class="text-danger">*</span></label>
                            <textarea name="symptoms" class="form-control" rows="3"
                                placeholder="Patient's main complaint...">{{ old('symptoms') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" class="form-control" rows="2"
                                placeholder="Diagnosis...">{{ old('diagnosis') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Clinical Notes</label>
                            <textarea name="clinical_notes" class="form-control" rows="3"
                                placeholder="Detailed clinical notes...">{{ old('clinical_notes') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Treatment Plan</label>
                            <textarea name="treatment_plan" class="form-control" rows="3"
                                placeholder="Treatment plan...">{{ old('treatment_plan') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Vital Signs --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0"><i class="bi bi-thermometer-half me-2"></i>Vital Signs</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Blood Pressure</label>
                            <input type="text" name="blood_pressure" class="form-control"
                                placeholder="e.g. 120/80" value="{{ old('blood_pressure') }}">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Temp (°C)</label>
                                <input type="number" name="temperature" class="form-control" step="0.1"
                                    min="30" max="45" value="{{ old('temperature') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Pulse (bpm)</label>
                                <input type="number" name="pulse" class="form-control"
                                    min="30" max="250" value="{{ old('pulse') }}">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Resp. Rate</label>
                                <input type="number" name="respiratory_rate" class="form-control"
                                    min="5" max="60" value="{{ old('respiratory_rate') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">SpO2 (%)</label>
                                <input type="number" name="oxygen_saturation" class="form-control" step="0.1"
                                    min="0" max="100" value="{{ old('oxygen_saturation') }}">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" name="weight" class="form-control" step="0.1"
                                    min="0" max="500" value="{{ old('weight') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" name="height" class="form-control" step="0.1"
                                    min="0" max="300" value="{{ old('height') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Follow-up --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0"><i class="bi bi-calendar-date me-2"></i>Follow-up</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control"
                                value="{{ old('follow_up_date') }}">
                        </div>
                        <div>
                            <label class="form-label">Follow-up Notes</label>
                            <textarea name="follow_up_notes" class="form-control" rows="2"
                                placeholder="Follow-up instructions...">{{ old('follow_up_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Save Consultation
            </button>
        </div>
    </form>
</x-auth-layout>
