<x-auth-layout>
    <x-page-header title="Today's Appointments" subtitle="{{ fmt_today() }}"
        :breadcrumbs="[['label' => 'Queue', 'url' => route('queue.index')], ['label' => 'Appointments']]">
        <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Queue
        </a>
    </x-page-header>

    {{-- Filter --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('queue.appointments') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Doctors</option>
                            @foreach (\App\Models\Doctor::orderBy('name')->get() as $doc)
                                <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto col-md-2">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results summary --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-calendar-check me-1"></i>{{ $appointments->count() }} appointment(s) for today
        </small>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Appointment #</th>
                        <th>Patient</th>
                        <th class="d-none d-md-table-cell">Doctor</th>
                        <th class="d-none d-lg-table-cell">Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $apt)
                        <tr>
                            <td class="fw-semibold">{{ fmt_time($apt->time) }}</td>
                            <td><span class="badge bg-primary">{{ $apt->appointment_number }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-primary">{{ initials($apt->patient?->name ?? $apt->name) }}</span>
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $apt->patient?->name ?? $apt->name }}</div>
                                        @if ($apt->patient)
                                            <small class="text-muted">{{ $apt->patient->patient_number }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $apt->doctor?->name ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell">
                                @if ($apt->department)
                                    <span class="badge bg-primary-subtle text-primary">{{ $apt->department->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $apt->status->badgeClass() }}">{{ $apt->status->label() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No appointments for today</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-auth-layout>
