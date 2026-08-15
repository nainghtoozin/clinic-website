<x-auth-layout>
    <x-page-header title="Appointments" subtitle="Manage appointments and their status workflow"
        :breadcrumbs="[['label' => 'Appointments']]">
        @can('appointment.create')
            <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> New Appointment
            </a>
        @endcan
    </x-page-header>

    @php
        $pendingCount = \App\Models\Appointment::where('status', \App\Enums\AppointmentStatus::Pending)->count();
    @endphp

    @if ($pendingCount > 0)
        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-bell-fill me-2"></i>
            <strong>{{ $pendingCount }}</strong> pending appointment request(s) require your attention.
        </div>
    @endif

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('appointments.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search apt #, patient name, phone..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All Doctors</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            @foreach (\App\Enums\AppointmentStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                                    {{ $s->label() }}
                                </option>
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
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'doctor_id', 'department_id', 'status', 'date_from', 'date_to']))
                            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results summary --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-calendar-check me-1"></i>{{ $appointments->total() }} appointment(s)
            @if (request()->has('search') && request('search'))
                @php($searchTerm = request('search'))
                &middot; matching &ldquo;{{ $searchTerm }}&rdquo;
            @endif
        </small>
        @if (request()->hasAny(['search', 'doctor_id', 'department_id', 'status', 'date_from', 'date_to']))
            <a href="{{ route('appointments.index') }}" class="small text-decoration-none">
                <i class="bi bi-slash-circle me-1"></i>Clear filters
            </a>
        @endif
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Appointment #</th>
                        <th>Patient</th>
                        <th class="d-none d-md-table-cell">Doctor</th>
                        <th class="d-none d-lg-table-cell">Department</th>
                        <th>Date / Time</th>
                        <th>Status</th>
                        <th class="d-none d-md-table-cell">Source</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr class="{{ $appointment->status->value === 'pending' ? 'table-warning' : '' }}">
                            <td>
                                <span class="badge bg-primary">{{ $appointment->appointment_number ?? 'Pending' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-primary">{{ initials($appointment->patient?->name ?? $appointment->name) }}</span>
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $appointment->patient?->name ?? $appointment->name }}</div>
                                        @if ($appointment->patient)
                                            <small class="text-muted">{{ $appointment->patient->patient_number }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if ($appointment->doctor)
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($appointment->doctor->profile_image)
                                            <img src="{{ Storage::url($appointment->doctor->profile_image) }}" class="avatar avatar-sm" alt="">
                                        @else
                                            <span class="avatar avatar-sm bg-info">{{ initials($appointment->doctor->name) }}</span>
                                        @endif
                                        <span>{{ $appointment->doctor->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                @if ($appointment->department)
                                    <span class="badge bg-primary-subtle text-primary">{{ $appointment->department->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ fmt_date($appointment->date) }}</span>
                                <small class="text-muted d-block">{{ $appointment->time ? fmt_time($appointment->time) : '-' }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $appointment->status->badgeClass() }}">
                                    {{ $appointment->status->label() }}
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if ($appointment->source === 'public')
                                    <span class="badge bg-info"><i class="bi bi-globe me-1"></i> Public</span>
                                @else
                                    <span class="badge bg-secondary"><i class="bi bi-building me-1"></i> Admin</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if ($appointment->isScheduled())
                                        @can('appointment.edit')
                                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('appointments.show', $appointment) }}#status" class="btn btn-sm btn-outline-success" title="Confirm">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        @endcan
                                        @can('appointment.cancel')
                                            <a href="{{ route('appointments.show', $appointment) }}#status" class="btn btn-sm btn-outline-danger" title="Cancel">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                        @endcan
                                    @endif
                                    @if ($appointment->status->value === 'pending')
                                        @can('appointment.edit')
                                            <a href="{{ route('appointments.show', $appointment) }}#status" class="btn btn-sm btn-success" title="Confirm Request">
                                                <i class="bi bi-check-lg me-1"></i> Confirm
                                            </a>
                                        @endcan
                                        @can('appointment.cancel')
                                            <a href="{{ route('appointments.show', $appointment) }}#status" class="btn btn-sm btn-danger" title="Reject Request">
                                                <i class="bi bi-x-lg me-1"></i> Reject
                                            </a>
                                        @endcan
                                    @endif
                                    @if (!$appointment->isCancelled() && !$appointment->isCompleted() && $appointment->status->value !== 'pending')
                                        @can('appointment.edit')
                                            <a href="{{ route('appointments.show', $appointment) }}#status" class="btn btn-sm btn-outline-secondary" title="Complete">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-check fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No appointments found</div>
                                <small>Try adjusting your search or filters.</small>
                                @can('appointment.create')
                                    <div class="mt-3">
                                        <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Book Appointment
                                        </a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($appointments->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $appointments->firstItem() }}&ndash;{{ $appointments->lastItem() }} of {{ $appointments->total() }}
            </small>
            <div>{{ $appointments->links() }}</div>
        </div>
    @endif
</x-auth-layout>
