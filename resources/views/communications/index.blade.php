<x-auth-layout>
    <x-page-header title="Communications" subtitle="Patient contact history and follow-ups"
        :breadcrumbs="[['label' => 'Communications']]">
        @can('communication.create')
            <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addCommunicationModal">
                <i class="bi bi-plus-circle me-1"></i> Log Communication
            </button>
        @endcan
        <a href="{{ route('communications.follow-ups') }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
            <i class="bi bi-bell me-1"></i> Follow-ups
        </a>
    </x-page-header>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('communications.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Patient</label>
                        <select name="patient_id" class="form-select form-select-sm">
                            <option value="">All Patients</option>
                            @foreach (\App\Models\Patient::orderBy('name')->get() as $patient)
                                <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                    {{ $patient->name }} ({{ $patient->patient_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Method</label>
                        <select name="contact_method" class="form-select form-select-sm">
                            <option value="">All Methods</option>
                            @foreach (\App\Models\Communication::CONTACT_METHODS as $key => $label)
                                <option value="{{ $key }}" {{ request('contact_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Purpose</label>
                        <select name="purpose" class="form-select form-select-sm">
                            <option value="">All Purposes</option>
                            @foreach (\App\Models\Communication::PURPOSES as $key => $label)
                                <option value="{{ $key }}" {{ request('purpose') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Outcome</label>
                        <select name="outcome" class="form-select form-select-sm">
                            <option value="">All Outcomes</option>
                            @foreach (\App\Models\Communication::OUTCOMES as $key => $label)
                                <option value="{{ $key }}" {{ request('outcome') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label small text-muted mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label small text-muted mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto col-md-1 d-flex gap-2">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i></button>
                        @if (request()->hasAny(['patient_id', 'contact_method', 'purpose', 'outcome', 'date_from', 'date_to']))
                            <a href="{{ route('communications.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-chat-dots me-1"></i>{{ $communications->total() }} communication(s)
        </small>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($communications->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-chat-dots fs-1 text-muted d-block mb-2"></i>
                    <h6 class="text-muted">No Communications Found</h6>
                    <p class="small text-muted mb-0">Log a patient communication to get started.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Method</th>
                                <th>Purpose</th>
                                <th class="d-none d-md-table-cell">Outcome</th>
                                <th class="d-none d-md-table-cell">Staff</th>
                                <th class="d-none d-md-table-cell">Follow-up</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($communications as $comm)
                                <tr>
                                    <td class="small">{{ $comm->contacted_at->format('d M Y') }}<br><span class="text-muted">{{ $comm->contacted_at->format('H:i') }}</span></td>
                                    <td>
                                        <div class="fw-semibold small">{{ $comm->patient->name ?? '-' }}</div>
                                        <div class="text-muted small">{{ $comm->patient->patient_number ?? '' }}</div>
                                    </td>
                                    <td><span class="badge {{ $comm->getContactMethodBadgeClass() }}">{{ $comm->contact_method_label }}</span></td>
                                    <td><span class="badge {{ $comm->getPurposeBadgeClass() }}">{{ $comm->purpose_label }}</span></td>
                                    <td class="d-none d-md-table-cell"><span class="badge {{ $comm->getOutcomeBadgeClass() }}">{{ $comm->outcome_label }}</span></td>
                                    <td class="d-none d-md-table-cell small">{{ $comm->user->name ?? '-' }}</td>
                                    <td class="d-none d-md-table-cell">
                                        @if ($comm->follow_up_date)
                                            @if ($comm->follow_up_completed)
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Done</span>
                                            @elseif ($comm->isFollowUpOverdue())
                                                <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>Overdue</span>
                                            @elseif ($comm->isFollowUpDueToday())
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Today</span>
                                            @else
                                                <span class="badge bg-info">{{ $comm->follow_up_date->format('d M') }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('communications.show', $comm) }}" class="btn btn-sm btn-outline-primary">
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

    @if ($communications->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $communications->withQueryString()->links() }}
        </div>
    @endif

    @can('communication.create')
        @include('communications._modal')
    @endcan
</x-auth-layout>
