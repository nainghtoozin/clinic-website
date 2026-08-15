<x-auth-layout>
    <x-page-header title="Edit Consultation" subtitle="{{ $consultation->patient?->name ?? '' }} &middot; {{ $consultation->patient?->patient_number ?? '' }}"
        :breadcrumbs="[['label' => 'Consultations', 'url' => route('consultations.index')], ['label' => 'Edit']]">
        <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
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

    <form method="POST" action="{{ route('consultations.update', $consultation) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Patient & Visit Info (read-only) --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient & Visit</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small mb-0">Patient #</label>
                                <div class="fw-semibold">{{ $consultation->patient?->patient_number ?? '-' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small mb-0">Name</label>
                                <div class="fw-semibold">{{ $consultation->patient?->name ?? '-' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small mb-0">Doctor</label>
                                <div>{{ $consultation->doctor?->name ?? '-' }}</div>
                            </div>
                            @if ($consultation->appointment)
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Appointment</label>
                                    <div><span class="badge bg-primary">{{ $consultation->appointment->appointment_number }}</span></div>
                                </div>
                            @endif
                            @if ($consultation->queueTicket)
                                <div class="col-6 col-md-4">
                                    <label class="form-label text-muted small mb-0">Queue Ticket</label>
                                    <div><span class="badge bg-info text-dark">{{ $consultation->queueTicket->ticket_number }}</span></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Clinical Information --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Clinical Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Symptoms / Chief Complaint</label>
                            <textarea name="symptoms" rows="3" class="form-control @error('symptoms') is-invalid @enderror"
                                placeholder="Patient's main complaint...">{{ old('symptoms', $consultation->symptoms) }}</textarea>
                            @error('symptoms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" rows="2" class="form-control @error('diagnosis') is-invalid @enderror"
                                placeholder="Diagnosis...">{{ old('diagnosis', $consultation->diagnosis) }}</textarea>
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Clinical Notes</label>
                            <textarea name="clinical_notes" rows="3" class="form-control @error('clinical_notes') is-invalid @enderror"
                                placeholder="Detailed clinical notes...">{{ old('clinical_notes', $consultation->clinical_notes) }}</textarea>
                            @error('clinical_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Treatment Plan</label>
                            <textarea name="treatment_plan" rows="3" class="form-control @error('treatment_plan') is-invalid @enderror"
                                placeholder="Treatment plan...">{{ old('treatment_plan', $consultation->treatment_plan) }}</textarea>
                            @error('treatment_plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Vital Signs --}}
                @php $vs = $consultation->vitalSign; @endphp
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-thermometer-half me-2"></i>Vital Signs</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Blood Pressure</label>
                            <input type="text" name="blood_pressure" class="form-control @error('blood_pressure') is-invalid @enderror"
                                placeholder="e.g. 120/80" value="{{ old('blood_pressure', $vs?->blood_pressure ?? '') }}">
                            @error('blood_pressure')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Temp (°C)</label>
                                <input type="number" name="temperature" class="form-control @error('temperature') is-invalid @enderror" step="0.1"
                                    min="30" max="45" value="{{ old('temperature', $vs?->temperature ?? '') }}">
                                @error('temperature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">Pulse (bpm)</label>
                                <input type="number" name="pulse" class="form-control @error('pulse') is-invalid @enderror"
                                    min="30" max="250" value="{{ old('pulse', $vs?->pulse ?? '') }}">
                                @error('pulse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Resp. Rate</label>
                                <input type="number" name="respiratory_rate" class="form-control @error('respiratory_rate') is-invalid @enderror"
                                    min="5" max="60" value="{{ old('respiratory_rate', $vs?->respiratory_rate ?? '') }}">
                                @error('respiratory_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">SpO2 (%)</label>
                                <input type="number" name="oxygen_saturation" class="form-control @error('oxygen_saturation') is-invalid @enderror" step="0.1"
                                    min="0" max="100" value="{{ old('oxygen_saturation', $vs?->oxygen_saturation ?? '') }}">
                                @error('oxygen_saturation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" name="weight" class="form-control @error('weight') is-invalid @enderror" step="0.1"
                                    min="0" max="500" value="{{ old('weight', $vs?->weight ?? '') }}">
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" name="height" class="form-control @error('height') is-invalid @enderror" step="0.1"
                                    min="0" max="300" value="{{ old('height', $vs?->height ?? '') }}">
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
                                value="{{ old('follow_up_date', $consultation->follow_up_date?->format('Y-m-d') ?? '') }}"
                                min="{{ now()->addDay()->format('Y-m-d') }}">
                            @error('follow_up_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Follow-up Notes</label>
                            <textarea name="follow_up_notes" class="form-control @error('follow_up_notes') is-invalid @enderror" rows="2"
                                placeholder="Follow-up instructions...">{{ old('follow_up_notes', $consultation->follow_up_notes) }}</textarea>
                            @error('follow_up_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
