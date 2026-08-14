<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Patient Profile</h5>
            <small class="text-muted">{{ $patient->patient_number }}</small>
        </div>
        <div class="d-flex gap-2">
            @can('appointment.create')
                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-calendar-plus me-1"></i> Book Appointment
                </a>
            @endcan
            @can('patient.edit')
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endcan
            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if ($patient->allergies)
        <div class="alert alert-danger py-2 mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Allergies:</strong> {{ $patient->allergies }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Personal Information</h6>
                    <span class="badge bg-{{ $patient->status === 'active' ? 'success' : ($patient->status === 'inactive' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($patient->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Patient Number</label>
                            <div class="fw-semibold">{{ $patient->patient_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Full Name</label>
                            <div class="fw-semibold">{{ $patient->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Phone</label>
                            <div>{{ $patient->phone ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Email</label>
                            <div>{{ $patient->email ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date of Birth</label>
                            <div>{{ $patient->date_of_birth ? fmt_date($patient->date_of_birth) : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Gender</label>
                            <div>{{ ucfirst($patient->gender ?? '-') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Blood Group</label>
                            <div>{{ $patient->blood_group ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Address</label>
                            <div>{{ $patient->address ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Appointment History</h6>
                </div>
                <div class="card-body p-0">
                    @if ($patient->appointments->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            No appointments found.
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
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($patient->appointments as $appointment)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $appointment->appointment_number }}</span>
                                            </td>
                                            <td>{{ fmt_date($appointment->date) }}</td>
                                            <td class="d-none d-md-table-cell">{{ $appointment->time ? fmt_time($appointment->time) : '-' }}</td>
                                            <td>{{ $appointment->doctor?->name ?? '-' }}</td>
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

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Consultation History</h6>
                </div>
                <div class="card-body p-0">
                    @if ($consultations->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                            No consultations found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th class="d-none d-md-table-cell">Doctor</th>
                                        <th>Diagnosis</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($consultations as $consultation)
                                        <tr>
                                            <td>{{ fmt_datetime($consultation->created_at) }}</td>
                                            <td class="d-none d-md-table-cell">{{ $consultation->doctor->name ?? '-' }}</td>
                                            <td>{{ Str::limit($consultation->diagnosis ?? '-', 30) }}</td>
                                            <td>
                                                <span class="badge {{ $consultation->isCompleted() ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ ucfirst($consultation->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2">
                            {{ $consultations->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="bi bi-file-medical me-2"></i>Prescription History</h6>
                </div>
                <div class="card-body p-0">
                    @if ($patient->prescriptions->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-file-medical fs-1 d-block mb-2"></i>
                            No prescriptions found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Prescription #</th>
                                        <th class="d-none d-md-table-cell">Doctor</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($patient->prescriptions as $prescription)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $prescription->prescription_number }}</span>
                                            </td>
                                            <td class="d-none d-md-table-cell">{{ $prescription->doctor->name ?? '-' }}</td>
                                            <td>{{ fmt_date($prescription->prescribed_date) }}</td>
                                            <td>
                                                <span class="badge bg-info text-dark">{{ $prescription->items->count() }} items</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-primary">
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

            @can('invoice.view')
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Billing History</h6>
                </div>
                <div class="card-body p-0">
                    @if ($patient->invoices->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                            No invoices found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Balance</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($patient->invoices as $invoice)
                                        <tr>
                                            <td><span class="badge bg-primary">{{ $invoice->invoice_number }}</span></td>
                                            <td>{{ fmt_datetime($invoice->created_at) }}</td>
                                            <td class="text-end">${{ number_format($invoice->total, 2) }}</td>
                                            <td class="text-end">
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

        <div class="col-lg-4">
            @if ($activeQueueTicket)
                <div class="card shadow-sm border-0 mb-4 border-start border-4 border-primary">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0"><i class="bi bi-list-ol me-2"></i>Today's Queue</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label text-muted small">Ticket Number</label>
                            <div class="fw-bold fs-5 text-primary">{{ $activeQueueTicket->ticket_number }}</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-muted small">Doctor</label>
                            <div>{{ $activeQueueTicket->doctor->name }}</div>
                        </div>
                        <div>
                            <label class="form-label text-muted small">Status</label>
                            <div>
                                @if ($activeQueueTicket->status === 'waiting')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>Waiting
                                    </span>
                                @elseif ($activeQueueTicket->status === 'called')
                                    <span class="badge bg-info">
                                        <i class="bi bi-megaphone me-1"></i>Called
                                    </span>
                                @elseif ($activeQueueTicket->status === 'in_consultation')
                                    <span class="badge bg-success">
                                        <i class="bi bi-chat-dots me-1"></i>In Consultation
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="bi bi-telephone me-2"></i>Emergency Contact</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Contact Name</label>
                        <div>{{ $patient->emergency_contact_name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="form-label text-muted small">Contact Phone</label>
                        <div>{{ $patient->emergency_contact_phone ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Medical Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Allergies</label>
                        <div>{{ $patient->allergies ?? 'None recorded' }}</div>
                    </div>
                    <div>
                        <label class="form-label text-muted small">Medical History</label>
                        <div>{{ $patient->medical_history ?? 'None recorded' }}</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Appointments</span>
                        <span class="fw-semibold">{{ $patient->appointments_count }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Registered</span>
                        <span>{{ fmt_datetime($patient->created_at) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated</span>
                        <span>{{ fmt_datetime($patient->updated_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
