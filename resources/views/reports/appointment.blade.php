<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Appointment Report</h4>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total</h6>
                    <h3>{{ $totalAppointments }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Scheduled</h6>
                    <h3 class="text-info">{{ $scheduledCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Completed</h6>
                    <h3 class="text-success">{{ $completedCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Cancelled</h6>
                    <h3 class="text-danger">{{ $cancelledCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Filters</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.appointment') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($doctors as $id => $name)
                                <option value="{{ $id }}" {{ request('doctor_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name/Number">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('reports.appointment') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Number</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td><small class="text-muted">{{ $appointment->appointment_number }}</small></td>
                                <td>{{ $appointment->name ?? $appointment->patient->name ?? '-' }}</td>
                                <td>{{ $appointment->doctor->name ?? '-' }}</td>
                                <td>{{ fmt_date($appointment->date) }}</td>
                                <td>{{ fmt_time($appointment->time) }}</td>
                                <td>
                                    <span class="badge {{ $appointment->status->badgeClass() }}">
                                        {{ $appointment->status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No appointments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($appointments->hasPages())
        <div class="card-footer bg-white">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
</x-auth-layout>
