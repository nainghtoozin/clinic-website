<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Appointments</h5>
        @can('appointment.create')
            <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> New Appointment
            </a>
        @endcan
    </div>

    @php
        $pendingCount = \App\Models\Appointment::where('status', \App\Enums\AppointmentStatus::Pending)->count();
    @endphp

    @if ($pendingCount > 0)
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-bell-fill me-2"></i>
            <strong>{{ $pendingCount }}</strong> pending appointment request(s) require your attention.
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('appointments.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search apt #, patient name, phone..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All Doctors</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            @foreach (\App\Enums\AppointmentStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                                    {{ $s->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Filter</button>
                    </div>
                    @if (request()->hasAny(['search', 'doctor_id', 'status', 'date_from', 'date_to']))
                        <div class="col-auto">
                            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Appointment #</th>
                            <th>Patient</th>
                            <th class="d-none d-md-table-cell">Doctor</th>
                            <th>Date</th>
                            <th class="d-none d-md-table-cell">Time</th>
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
                                <td>{{ $appointment->patient?->name ?? $appointment->name }}</td>
                                <td class="d-none d-md-table-cell">{{ $appointment->doctor->name }}</td>
                                <td>{{ fmt_date($appointment->date) }}</td>
                                <td class="d-none d-md-table-cell">{{ $appointment->time ? fmt_time($appointment->time) : '-' }}</td>
                                <td>
                                    <span class="badge {{ $appointment->status->badgeClass() }}">
                                        {{ $appointment->status->label() }}
                                    </span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if ($appointment->source === 'public')
                                        <span class="badge bg-info">
                                            <i class="bi bi-globe me-1"></i> Public
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-building me-1"></i> Admin
                                        </span>
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
                                                    <i class="bi bi-check-lg"></i> Confirm
                                                </a>
                                            @endcan
                                            @can('appointment.cancel')
                                                <a href="{{ route('appointments.show', $appointment) }}#status" class="btn btn-sm btn-danger" title="Reject Request">
                                                    <i class="bi bi-x-lg"></i> Reject
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
                                    <div>No appointments found.</div>
                                    @can('appointment.create')
                                        <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-primary mt-2">
                                            <i class="bi bi-plus-lg me-1"></i> Book Appointment
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $appointments->links() }}
    </div>
</x-auth-layout>
