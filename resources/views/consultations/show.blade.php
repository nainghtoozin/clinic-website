<x-auth-layout>
    <x-page-header title="Consultation Details" subtitle="{{ $consultation->patient?->name ?? '' }} &middot; {{ fmt_datetime($consultation->created_at) }}"
        :breadcrumbs="[['label' => 'Consultations', 'url' => route('consultations.index')], ['label' => $consultation->patient?->name ?? 'Consultation']]">
        @can('prescription.create')
            <a href="{{ route('prescriptions.create', ['consultation_id' => $consultation->id]) }}" class="btn btn-info btn-sm d-inline-flex align-items-center">
                <i class="bi bi-file-medical me-1"></i> Add Prescription
            </a>
        @endcan
        @if ($consultation->isDraft())
            @can('consultation.complete')
                <form method="POST" action="{{ route('consultations.complete', $consultation) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-success btn-sm d-inline-flex align-items-center"
                        onclick="return confirm('Complete this consultation? This will also complete the queue ticket and appointment.')">
                        <i class="bi bi-check-circle me-1"></i> Save & Complete Visit
                    </button>
                </form>
            @endcan
            @can('consultation.edit')
                <a href="{{ route('consultations.edit', $consultation) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endcan
        @endif
        <a href="{{ route('consultations.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    <div class="row">
        <div class="col-lg-8">
            {{-- Patient & Visit Info --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient & Visit</h6>
                    <span class="badge {{ $consultation->isCompleted() ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ ucfirst($consultation->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Patient #</label>
                            <div class="fw-semibold">{{ $consultation->patient->patient_number ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Patient Name</label>
                            <div class="fw-semibold">{{ $consultation->patient->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Doctor</label>
                            <div>{{ $consultation->doctor->name ?? '-' }}</div>
                        </div>
                        @if ($consultation->appointment)
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Appointment #</label>
                                <div><span class="badge bg-primary">{{ $consultation->appointment->appointment_number }}</span></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Visit Date</label>
                                <div>{{ fmt_date($consultation->appointment->date) }}</div>
                            </div>
                        @endif
                        @if ($consultation->queueTicket)
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Queue Ticket</label>
                                <div><span class="badge bg-info text-dark">{{ $consultation->queueTicket->ticket_number }}</span></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Clinical Information --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Clinical Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small">Symptoms / Chief Complaint</label>
                            <div class="border rounded p-3 bg-light">{{ $consultation->symptoms ?? 'Not recorded' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Diagnosis</label>
                            <div class="border rounded p-3 bg-light">{{ $consultation->diagnosis ?? 'Not recorded' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Treatment Plan</label>
                            <div class="border rounded p-3 bg-light">{{ $consultation->treatment_plan ?? 'Not recorded' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Clinical Notes</label>
                            <div class="border rounded p-3 bg-light">{{ $consultation->clinical_notes ?? 'Not recorded' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Follow-up --}}
            @if ($consultation->follow_up_date || $consultation->follow_up_notes)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calendar-date me-2"></i>Follow-up</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if ($consultation->follow_up_date)
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Follow-up Date</label>
                                    <div class="fw-semibold">{{ fmt_date($consultation->follow_up_date) }}</div>
                                </div>
                            @endif
                            @if ($consultation->follow_up_notes)
                                <div class="col-12">
                                    <label class="form-label text-muted small">Follow-up Notes</label>
                                    <div class="border rounded p-3 bg-light">{{ $consultation->follow_up_notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Vital Signs --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-thermometer-half me-2"></i>Vital Signs</h6>
                </div>
                <div class="card-body">
                    @if ($consultation->vitalSign)
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label text-muted small">Blood Pressure</label>
                                <div class="fw-semibold">{{ $consultation->vitalSign->blood_pressure ?? '-' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Temperature</label>
                                <div class="fw-semibold">{{ $consultation->vitalSign->temperature ? $consultation->vitalSign->temperature . ' °C' : '-' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Pulse</label>
                                <div class="fw-semibold">{{ $consultation->vitalSign->pulse ? $consultation->vitalSign->pulse . ' bpm' : '-' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Resp. Rate</label>
                                <div class="fw-semibold">{{ $consultation->vitalSign->respiratory_rate ? $consultation->vitalSign->respiratory_rate . ' /min' : '-' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">SpO2</label>
                                <div class="fw-semibold">{{ $consultation->vitalSign->oxygen_saturation ? $consultation->vitalSign->oxygen_saturation . ' %' : '-' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Weight</label>
                                <div class="fw-semibold">{{ $consultation->vitalSign->weight ? $consultation->vitalSign->weight . ' kg' : '-' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Height</label>
                                <div class="fw-semibold">{{ $consultation->vitalSign->height ? $consultation->vitalSign->height . ' cm' : '-' }}</div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No vital signs recorded
                        </div>
                    @endif
                </div>
            </div>

            {{-- Prescriptions --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-file-medical me-2"></i>Prescriptions</h6>
                    @can('prescription.create')
                        <a href="{{ route('prescriptions.create', ['consultation_id' => $consultation->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($consultation->prescriptions->isEmpty())
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No prescriptions yet
                        </div>
                    @else
                        @foreach ($consultation->prescriptions as $prescription)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <a href="{{ route('prescriptions.show', $prescription) }}" class="fw-semibold text-decoration-none">
                                        {{ $prescription->prescription_number }}
                                    </a>
                                    <div class="small text-muted">{{ fmt_date($prescription->prescribed_date) }}</div>
                                </div>
                                <span class="badge bg-info text-dark">{{ $prescription->items->count() }} items</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Investigations --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-clipboard2-data me-2"></i>Investigations</h6>
                    @can('investigation.create')
                        <a href="{{ route('investigations.create', ['consultation_id' => $consultation->id, 'patient_id' => $consultation->patient_id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($consultation->investigations->isEmpty())
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No investigations yet
                        </div>
                    @else
                        @foreach ($consultation->investigations as $inv)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <a href="{{ route('investigations.show', $inv) }}" class="fw-semibold text-decoration-none">
                                        {{ $inv->labTest->name ?? '-' }}
                                    </a>
                                    <div class="small text-muted">{{ $inv->requested_date ? fmt_date($inv->requested_date) : '' }}</div>
                                </div>
                                <span class="badge {{ $inv->getStatusBadgeClass() }}">{{ $inv->getStatusLabel() }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Billing --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Billing</h6>
                    @if (!$consultation->invoice && $consultation->isCompleted())
                        @can('invoice.create')
                            <a href="{{ route('invoices.create', ['consultation_id' => $consultation->id]) }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-plus-circle me-1"></i> Create Invoice
                            </a>
                        @endcan
                    @endif
                </div>
                <div class="card-body">
                    @if ($consultation->invoice)
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('invoices.show', $consultation->invoice) }}" class="fw-semibold text-decoration-none">
                                    {{ $consultation->invoice->invoice_number }}
                                </a>
                                <div class="small text-muted">Total: ${{ number_format($consultation->invoice->total, 2) }}</div>
                            </div>
                            <span class="badge {{ $consultation->invoice->getStatusBadgeClass() }}">
                                {{ $consultation->invoice->getStatusLabel() }}
                            </span>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            @if ($consultation->isCompleted())
                                No invoice yet
                            @else
                                Complete consultation to create invoice
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
