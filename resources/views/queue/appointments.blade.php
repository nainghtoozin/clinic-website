<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Today's Appointments</h4>
            <small class="text-muted">{{ fmt_today() }}</small>
        </div>
        <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Queue
        </a>
    </div>

    <form method="GET" action="{{ route('queue.appointments') }}">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Filter by Doctor</label>
                        <select name="doctor_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Doctors</option>
                            @foreach (\App\Models\Doctor::orderBy('name')->get() as $doc)
                                <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Appointment #</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $apt)
                        <tr>
                            <td class="fw-semibold">
                                {{ fmt_time($apt->time) }}
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $apt->appointment_number }}</span>
                            </td>
                            <td>{{ $apt->patient?->name ?? $apt->name }}</td>
                            <td>{{ $apt->doctor->name }}</td>
                            <td>
                                <span class="badge {{ $apt->status->badgeClass() }}">
                                    {{ $apt->status->label() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No appointments for today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-auth-layout>
