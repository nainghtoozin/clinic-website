<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Edit Consultation</h4>
            <small class="text-muted">{{ $consultation->patient->name ?? '' }}</small>
        </div>
        <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('consultations.update', $consultation) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                {{-- Patient Info (read-only) --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Patient #</label>
                                <div class="fw-semibold">{{ $consultation->patient->patient_number ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Name</label>
                                <div class="fw-semibold">{{ $consultation->patient->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Doctor</label>
                                <div>{{ $consultation->doctor->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clinical Information --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Clinical Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Chief Complaint / Symptoms</label>
                            <textarea name="symptoms" class="form-control" rows="3"
                                placeholder="Patient's main complaint...">{{ old('symptoms', $consultation->symptoms) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" class="form-control" rows="2"
                                placeholder="Diagnosis...">{{ old('diagnosis', $consultation->diagnosis) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Clinical Notes</label>
                            <textarea name="clinical_notes" class="form-control" rows="3"
                                placeholder="Detailed clinical notes...">{{ old('clinical_notes', $consultation->clinical_notes) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Treatment Plan</label>
                            <textarea name="treatment_plan" class="form-control" rows="3"
                                placeholder="Treatment plan...">{{ old('treatment_plan', $consultation->treatment_plan) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Vital Signs --}}
                @php $vs = $consultation->vitalSign; @endphp
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-thermometer-half me-2"></i>Vital Signs</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Blood Pressure</label>
                            <input type="text" name="blood_pressure" class="form-control"
                                placeholder="e.g. 120/80" value="{{ old('blood_pressure', $vs->blood_pressure ?? '') }}">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Temp (°C)</label>
                                <input type="number" name="temperature" class="form-control" step="0.1"
                                    min="30" max="45" value="{{ old('temperature', $vs->temperature ?? '') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Pulse (bpm)</label>
                                <input type="number" name="pulse" class="form-control"
                                    min="30" max="250" value="{{ old('pulse', $vs->pulse ?? '') }}">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Resp. Rate</label>
                                <input type="number" name="respiratory_rate" class="form-control"
                                    min="5" max="60" value="{{ old('respiratory_rate', $vs->respiratory_rate ?? '') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">SpO2 (%)</label>
                                <input type="number" name="oxygen_saturation" class="form-control" step="0.1"
                                    min="0" max="100" value="{{ old('oxygen_saturation', $vs->oxygen_saturation ?? '') }}">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" name="weight" class="form-control" step="0.1"
                                    min="0" max="500" value="{{ old('weight', $vs->weight ?? '') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" name="height" class="form-control" step="0.1"
                                    min="0" max="300" value="{{ old('height', $vs->height ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Follow-up --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calendar-date me-2"></i>Follow-up</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control"
                                value="{{ old('follow_up_date', $consultation->follow_up_date?->format('Y-m-d') ?? '') }}">
                        </div>
                        <div>
                            <label class="form-label">Follow-up Notes</label>
                            <textarea name="follow_up_notes" class="form-control" rows="2"
                                placeholder="Follow-up instructions...">{{ old('follow_up_notes', $consultation->follow_up_notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Save Changes
            </button>
        </div>
    </form>
</x-auth-layout>
