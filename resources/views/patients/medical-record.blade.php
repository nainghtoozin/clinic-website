<x-auth-layout>
    <x-page-header title="Patient Medical Record" subtitle="{{ $patient->patient_number }}"
        :breadcrumbs="[['label' => 'Patients', 'url' => route('patients.index')], ['label' => $patient->name, 'url' => route('patients.show', $patient)], ['label' => 'Medical Record']]">
        @can('appointment.create')
            <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
            </a>
        @endcan
        @can('patient.edit')
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endcan
        @can('patient.view')
            <a href="{{ route('print.medical-record', $patient) }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-printer me-1"></i> Print Record
            </a>
        @endcan
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Profile
        </a>
    </x-page-header>

    @if ($patient->allergies)
        <div class="alert alert-danger py-2 mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Allergies:</strong> {{ $patient->allergies }}
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-start gap-3 p-3">
                <span class="avatar bg-primary" style="width:64px;height:64px;font-size:1.5rem;border-radius:16px;">
                    {{ initials($patient->name) }}
                </span>
                <div class="flex-grow-1 min-w-0">
                    <h4 class="mb-1">{{ $patient->name }}</h4>
                    <div class="text-muted small mb-2">
                        {{ $patient->patient_number }}
                        <span class="mx-1">·</span>
                        <span class="badge {{ $patient->status === 'active' ? 'bg-success' : ($patient->status === 'inactive' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ ucfirst($patient->status) }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-3 small">
                        @if ($patient->date_of_birth)
                            <span><i class="bi bi-calendar3 me-1 text-muted"></i>Age: {{ \Illuminate\Support\Carbon::parse($patient->date_of_birth)->age }}</span>
                        @endif
                        @if ($patient->gender)
                            <span><i class="bi bi-person me-1 text-muted"></i>{{ ucfirst($patient->gender) }}</span>
                        @endif
                        @if ($patient->phone)
                            <span><i class="bi bi-telephone me-1 text-muted"></i>{{ $patient->phone }}</span>
                        @endif
                        @if ($patient->blood_group)
                            <span class="badge bg-danger-subtle text-danger"><i class="bi bi-droplet me-1"></i>{{ $patient->blood_group }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($patient->allergies || $patient->medical_history)
        <div class="row g-3 mb-4">
            @if ($patient->allergies)
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 border-start border-4 border-danger">
                        <div class="card-body py-3">
                            <h6 class="mb-2 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Allergies</h6>
                            <div class="small">{{ $patient->allergies }}</div>
                        </div>
                    </div>
                </div>
            @endif
            @if ($patient->medical_history)
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 border-start border-4 border-warning">
                        <div class="card-body py-3">
                            <h6 class="mb-2 text-warning"><i class="bi bi-clock-history me-1"></i> Medical History</h6>
                            <div class="small">{{ $patient->medical_history }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <ul class="nav nav-pills nav-fill mb-4" id="medicalRecordTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-visits" type="button" role="tab">
                <i class="bi bi-clipboard2-pulse me-1 d-none d-sm-inline"></i> Visits
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-vitals" type="button" role="tab">
                <i class="bi bi-heart-pulse me-1 d-none d-sm-inline"></i> Vitals
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-prescriptions" type="button" role="tab">
                <i class="bi bi-file-medical me-1 d-none d-sm-inline"></i> Prescriptions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-appointments" type="button" role="tab">
                <i class="bi bi-calendar-check me-1 d-none d-sm-inline"></i> Appointments
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-billing" type="button" role="tab">
                <i class="bi bi-receipt me-1 d-none d-sm-inline"></i> Billing
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-investigations" type="button" role="tab">
                <i class="bi bi-clipboard2-data me-1 d-none d-sm-inline"></i> Investigations
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-communications" type="button" role="tab">
                <i class="bi bi-chat-dots me-1 d-none d-sm-inline"></i> Communications
            </button>
        </li>
    </ul>

    <div class="tab-content" id="medicalRecordTabContent">
        <div class="tab-pane fade show active" id="tab-visits" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Visits</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('patients.medical-record', $patient) }}" class="row g-2 align-items-end">
                        <div class="col-md-2 col-6">
                            <label class="form-label small text-muted">From Date</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label small text-muted">To Date</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label small text-muted">Doctor</label>
                            <select name="doctor_id" class="form-select form-select-sm">
                                <option value="">All Doctors</option>
                                @foreach ($doctors as $doc)
                                    <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label small text-muted">Type</label>
                            <select name="record_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="consultation" {{ request('record_type') === 'consultation' ? 'selected' : '' }}>With Diagnosis</option>
                                <option value="prescription" {{ request('record_type') === 'prescription' ? 'selected' : '' }}>With Prescription</option>
                                <option value="vitals" {{ request('record_type') === 'vitals' ? 'selected' : '' }}>With Vitals</option>
                                <option value="invoice" {{ request('record_type') === 'invoice' ? 'selected' : '' }}>With Invoice</option>
                                <option value="followup" {{ request('record_type') === 'followup' ? 'selected' : '' }}>Follow-up</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i> Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($consultations->isEmpty())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-clipboard-x fs-1 text-muted d-block mb-2"></i>
                        <h6 class="text-muted mb-1">No Visit Records</h6>
                        <p class="small text-muted mb-0">No consultations found for this patient{{ request()->hasAny(['date_from','date_to','doctor_id','status','record_type']) ? ' matching the selected filters' : '' }}.</p>
                    </div>
                </div>
            @else
                @foreach ($consultations as $consultation)
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="bi bi-clipboard2-pulse me-1 text-primary"></i>
                                        {{ $consultation->created_at->format('M d, Y') }}
                                        <span class="text-muted small fw-normal">at {{ $consultation->created_at->format('h:i A') }}</span>
                                    </h6>
                                    <div class="small text-muted">
                                        Dr. {{ $consultation->doctor->name ?? '-' }}
                                        @if ($consultation->appointment?->department)
                                            <span class="mx-1">·</span>
                                            {{ $consultation->appointment->department->name }}
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    @if ($consultation->appointment?->appointment_number)
                                        <span class="badge bg-primary-subtle text-primary">{{ $consultation->appointment->appointment_number }}</span>
                                    @endif
                                    <span class="badge {{ $consultation->isCompleted() ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                        {{ ucfirst($consultation->status) }}
                                    </span>
                                </div>
                            </div>

                            @if ($consultation->symptoms)
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-0">Symptoms</label>
                                    <div class="small">{{ $consultation->symptoms }}</div>
                                </div>
                            @endif

                            @if ($consultation->diagnosis)
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-0">Diagnosis</label>
                                    <div class="small fw-semibold">{{ $consultation->diagnosis }}</div>
                                </div>
                            @endif

                            @if ($consultation->treatment_plan)
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-0">Treatment</label>
                                    <div class="small">{{ $consultation->treatment_plan }}</div>
                                </div>
                            @endif

                            @if ($consultation->follow_up_date || $consultation->follow_up_notes)
                                <div class="border-top mt-2 pt-2">
                                    <label class="form-label text-muted small mb-0">
                                        <i class="bi bi-calendar-date me-1"></i>Follow-up
                                        @if ($consultation->follow_up_date)
                                            <span class="fw-semibold text-primary">{{ fmt_date($consultation->follow_up_date) }}</span>
                                        @endif
                                    </label>
                                    @if ($consultation->follow_up_notes)
                                        <div class="small text-muted">{{ $consultation->follow_up_notes }}</div>
                                    @endif
                                </div>
                            @endif

                            @if ($consultation->vitalSign)
                                <div class="border-top mt-2 pt-2">
                                    <label class="form-label text-muted small mb-1"><i class="bi bi-heart-pulse me-1"></i>Vital Signs</label>
                                    <div class="d-flex flex-wrap gap-2 small">
                                        @if ($consultation->vitalSign->blood_pressure)
                                            <span class="badge bg-light text-dark border">BP: {{ $consultation->vitalSign->blood_pressure }}</span>
                                        @endif
                                        @if ($consultation->vitalSign->temperature)
                                            <span class="badge bg-light text-dark border">Temp: {{ $consultation->vitalSign->temperature }}°C</span>
                                        @endif
                                        @if ($consultation->vitalSign->pulse)
                                            <span class="badge bg-light text-dark border">Pulse: {{ $consultation->vitalSign->pulse }} bpm</span>
                                        @endif
                                        @if ($consultation->vitalSign->weight)
                                            <span class="badge bg-light text-dark border">Wt: {{ $consultation->vitalSign->weight }} kg</span>
                                        @endif
                                        @if ($consultation->vitalSign->height)
                                            <span class="badge bg-light text-dark border">Ht: {{ $consultation->vitalSign->height }} cm</span>
                                        @endif
                                        @if ($consultation->vitalSign->oxygen_saturation)
                                            <span class="badge bg-light text-dark border">SpO2: {{ $consultation->vitalSign->oxygen_saturation }}%</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex gap-2 mt-2">
                                <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View Full Details
                                </a>
                                @if ($consultation->prescriptions->isNotEmpty())
                                    <span class="badge bg-info-subtle text-info align-self-center">
                                        <i class="bi bi-file-medical me-1"></i>{{ $consultation->prescriptions->count() }} prescription(s)
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-center">
                    {{ $consultations->withQueryString()->links() }}
                </div>
            @endif
        </div>

        <div class="tab-pane fade" id="tab-vitals" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Vital Signs History</h6>
                </div>
                <div class="card-body p-0">
                    @if ($vitalSigns->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-heart-pulse fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted mb-1">No Vital Signs Recorded</h6>
                            <p class="small text-muted mb-0">Vital signs will appear here once recorded during consultations.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th class="d-none d-md-table-cell">Doctor</th>
                                        <th>BP</th>
                                        <th>Temp</th>
                                        <th>Pulse</th>
                                        <th class="d-none d-lg-table-cell">SpO2</th>
                                        <th class="d-none d-lg-table-cell">Weight</th>
                                        <th class="d-none d-lg-table-cell">Height</th>
                                        <th class="d-none d-lg-table-cell">BMI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vitalSigns as $vital)
                                        @php
                                            $bmi = null;
                                            if ($vital->weight && $vital->height && $vital->height > 0) {
                                                $heightM = $vital->height / 100;
                                                $bmi = round($vital->weight / ($heightM * $heightM), 1);
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="small">{{ fmt_datetime($vital->recorded_at) }}</div>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <span class="small">Dr. {{ $vital->consultation?->doctor?->name ?? '-' }}</span>
                                            </td>
                                            <td><span class="small fw-semibold">{{ $vital->blood_pressure ?? '-' }}</span></td>
                                            <td><span class="small">{{ $vital->temperature ? $vital->temperature . '°C' : '-' }}</span></td>
                                            <td><span class="small">{{ $vital->pulse ? $vital->pulse . ' bpm' : '-' }}</span></td>
                                            <td class="d-none d-lg-table-cell"><span class="small">{{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : '-' }}</span></td>
                                            <td class="d-none d-lg-table-cell"><span class="small">{{ $vital->weight ? $vital->weight . ' kg' : '-' }}</span></td>
                                            <td class="d-none d-lg-table-cell"><span class="small">{{ $vital->height ? $vital->height . ' cm' : '-' }}</span></td>
                                            <td class="d-none d-lg-table-cell">
                                                @if ($bmi)
                                                    <span class="small {{ $bmi >= 25 ? 'text-warning' : ($bmi < 18.5 ? 'text-info' : 'text-success') }}">{{ $bmi }}</span>
                                                @else
                                                    <span class="small text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-prescriptions" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-file-medical me-2"></i>Prescription History</h6>
                </div>
                <div class="card-body p-0">
                    @if ($patient->prescriptions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-file-medical fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted mb-1">No Prescriptions</h6>
                            <p class="small text-muted mb-0">Prescriptions will appear here once issued.</p>
                        </div>
                    @else
                        @foreach ($patient->prescriptions as $prescription)
                            <div class="border-bottom p-3 {{ $loop->last ? 'border-bottom-0' : '' }}">
                                <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-primary">{{ $prescription->prescription_number }}</span>
                                            <span class="small text-muted">{{ fmt_date($prescription->prescribed_date) }}</span>
                                        </div>
                                        <div class="small text-muted">
                                            Dr. {{ $prescription->doctor->name ?? '-' }}
                                            @if ($prescription->consultation)
                                                <span class="mx-1">·</span>
                                                <a href="{{ route('consultations.show', $prescription->consultation) }}" class="text-decoration-none">View Consultation</a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge {{ $prescription->isDispensed() ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ ucfirst($prescription->status ?? 'pending') }}
                                        </span>
                                        <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                @if ($prescription->items->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr class="text-muted small">
                                                    <th>Medicine</th>
                                                    <th>Dosage</th>
                                                    <th class="d-none d-md-table-cell">Frequency</th>
                                                    <th class="d-none d-md-table-cell">Duration</th>
                                                    <th>Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($prescription->items as $item)
                                                    <tr>
                                                        <td class="small fw-semibold">{{ $item->medicine->name ?? '-' }}</td>
                                                        <td class="small">{{ $item->dosage ?? '-' }}</td>
                                                        <td class="small d-none d-md-table-cell">{{ $item->frequency ?? '-' }}</td>
                                                        <td class="small d-none d-md-table-cell">{{ $item->duration ?? '-' }}</td>
                                                        <td class="small">{{ $item->quantity ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                                @if ($prescription->notes)
                                    <div class="small text-muted mt-1"><i class="bi bi-info-circle me-1"></i>{{ $prescription->notes }}</div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-appointments" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Appointment History</h6>
                </div>
                <div class="card-body p-0">
                    @if ($patient->appointments->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted mb-1">No Appointments</h6>
                            <p class="small text-muted mb-0">Appointment history will appear here.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Appointment #</th>
                                        <th>Date</th>
                                        <th class="d-none d-md-table-cell">Time</th>
                                        <th>Doctor</th>
                                        <th class="d-none d-lg-table-cell">Department</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($patient->appointments as $appointment)
                                        <tr>
                                            <td><span class="badge bg-primary">{{ $appointment->appointment_number }}</span></td>
                                            <td class="small">{{ fmt_date($appointment->date) }}</td>
                                            <td class="small d-none d-md-table-cell">{{ $appointment->time ? fmt_time($appointment->time) : '-' }}</td>
                                            <td class="small">{{ $appointment->doctor?->name ?? '-' }}</td>
                                            <td class="small d-none d-lg-table-cell">{{ $appointment->department?->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge {{ $appointment->status->badgeClass() }}">
                                                    {{ $appointment->status->label() }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-billing" role="tabpanel">
            @can('invoice.view')
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Billing History</h6>
                    </div>
                    <div class="card-body p-0">
                        @if ($patient->invoices->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-receipt fs-1 text-muted d-block mb-2"></i>
                                <h6 class="text-muted mb-1">No Billing Records</h6>
                                <p class="small text-muted mb-0">Invoices and payments will appear here.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end d-none d-md-table-cell">Paid</th>
                                            <th class="text-end">Balance</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($patient->invoices as $invoice)
                                            <tr>
                                                <td><span class="badge bg-primary">{{ $invoice->invoice_number }}</span></td>
                                                <td class="small">{{ fmt_datetime($invoice->created_at) }}</td>
                                                <td class="text-end small">${{ number_format($invoice->total, 2) }}</td>
                                                <td class="text-end small d-none d-md-table-cell">${{ number_format($invoice->amount_paid, 2) }}</td>
                                                <td class="text-end small">
                                                    @if ($invoice->balance > 0)
                                                        <span class="text-danger">${{ number_format($invoice->balance, 2) }}</span>
                                                    @else
                                                        <span class="text-success">$0.00</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge {{ $invoice->getStatusBadgeClass() }}">{{ $invoice->getStatusLabel() }}</span></td>
                                                <td><a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endcan
        </div>

        <div class="tab-pane fade" id="tab-investigations" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0"><i class="bi bi-clipboard2-data me-2"></i>Lab Investigations</h6>
                    @can('investigation.create')
                        <a href="{{ route('investigations.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> New Investigation
                        </a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @if ($investigations->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-clipboard2-data fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted mb-1">No Investigations</h6>
                            <p class="small text-muted mb-0">Lab investigations will appear here.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Test</th>
                                        <th class="d-none d-md-table-cell">Doctor</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($investigations as $inv)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold small">{{ $inv->labTest->name ?? '-' }}</div>
                                                <div class="text-muted small">{{ $inv->labTest->code ?? '' }}</div>
                                            </td>
                                            <td class="d-none d-md-table-cell small">Dr. {{ $inv->doctor->name ?? '-' }}</td>
                                            <td class="small">{{ $inv->requested_date ? fmt_date($inv->requested_date) : '-' }}</td>
                                            <td><span class="badge {{ $inv->getStatusBadgeClass() }}">{{ $inv->getStatusLabel() }}</span></td>
                                            <td><a href="{{ route('investigations.show', $inv) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $investigations->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-communications" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Communication History</h6>
                    @can('communication.create')
                        <a href="{{ route('communications.index', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> View All
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($patientComms->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-chat-dots fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted">No Communications</h6>
                            <p class="small text-muted mb-0">No communication records for this patient yet.</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($patientComms as $comm)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <span class="badge {{ $comm->getContactMethodBadgeClass() }} me-1">{{ $comm->contact_method_label }}</span>
                                            <span class="badge {{ $comm->getPurposeBadgeClass() }} me-1">{{ $comm->purpose_label }}</span>
                                            <span class="badge {{ $comm->getOutcomeBadgeClass() }}">{{ $comm->outcome_label }}</span>
                                        </div>
                                        <small class="text-muted">{{ $comm->contacted_at->format('d M, H:i') }}</small>
                                    </div>
                                    @if ($comm->note)
                                        <div class="small text-muted">{{ Str::limit($comm->note, 120) }}</div>
                                    @endif
                                    @if ($comm->follow_up_date && !$comm->follow_up_completed)
                                        <div class="small mt-1">
                                            <i class="bi bi-bell text-warning me-1"></i>
                                            Follow-up: {{ $comm->follow_up_date->format('d M Y') }}
                                            @if ($comm->isFollowUpOverdue()) <span class="badge bg-danger ms-1">Overdue</span> @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($activeQueueTicket)
                <div class="card shadow-sm border-0 mb-4 border-start border-4 border-primary">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0"><i class="bi bi-list-ol me-2"></i>Today's Queue</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-3">
                            <div>
                                <label class="form-label text-muted small">Ticket</label>
                                <div class="fw-bold fs-5 text-primary">{{ $activeQueueTicket->ticket_number }}</div>
                            </div>
                            <div>
                                <label class="form-label text-muted small">Doctor</label>
                                <div>{{ $activeQueueTicket->doctor->name }}</div>
                            </div>
                            <div>
                                <label class="form-label text-muted small">Status</label>
                                <div>
                                    @if ($activeQueueTicket->status === 'waiting')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Waiting</span>
                                    @elseif ($activeQueueTicket->status === 'called')
                                        <span class="badge bg-info"><i class="bi bi-megaphone me-1"></i>Called</span>
                                    @elseif ($activeQueueTicket->status === 'in_consultation')
                                        <span class="badge bg-success"><i class="bi bi-chat-dots me-1"></i>In Consultation</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>