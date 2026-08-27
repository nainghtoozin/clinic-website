<x-auth-layout>
    <x-page-header title="Investigations" subtitle="Lab investigation requests and results"
        :breadcrumbs="[['label' => 'Investigations']]">
        @can('investigation.create')
            <a href="{{ route('investigations.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-circle me-1"></i> New Investigation
            </a>
        @endcan
    </x-page-header>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('investigations.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Patient or test name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="requested" {{ request('status') === 'requested' ? 'selected' : '' }}>Requested</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All Doctors</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto col-md-1 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel"></i>
                        </button>
                        @if (request()->hasAny(['search', 'status', 'doctor_id', 'date_from', 'date_to']))
                            <a href="{{ route('investigations.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
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
            <i class="bi bi-clipboard2-data me-1"></i>{{ $investigations->total() }} investigation(s)
        </small>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($investigations->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-clipboard2-data fs-1 text-muted d-block mb-2"></i>
                    <h6 class="text-muted">No Investigations Found</h6>
                    <p class="small text-muted mb-0">Create an investigation request to get started.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Test</th>
                                <th class="d-none d-md-table-cell">Doctor</th>
                                <th>Date</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($investigations as $inv)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $inv->patient->name ?? '-' }}</div>
                                        <div class="text-muted small">{{ $inv->patient->patient_number ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $inv->labTest->name ?? '-' }}</div>
                                        <div class="text-muted small">{{ $inv->labTest->code ?? '' }}</div>
                                    </td>
                                    <td class="d-none d-md-table-cell small">Dr. {{ $inv->doctor->name ?? '-' }}</td>
                                    <td class="small">{{ $inv->requested_date ? fmt_date($inv->requested_date) : '-' }}</td>
                                    <td><span class="badge {{ $inv->getPriorityBadgeClass() }}">{{ ucfirst($inv->priority) }}</span></td>
                                    <td><span class="badge {{ $inv->getStatusBadgeClass() }}">{{ $inv->getStatusLabel() }}</span></td>
                                    <td>
                                        <a href="{{ route('investigations.show', $inv) }}" class="btn btn-sm btn-outline-primary">
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

    @if ($investigations->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $investigations->withQueryString()->links() }}
        </div>
    @endif
</x-auth-layout>
