<x-auth-layout>
    <x-page-header title="Check-in Appointment" subtitle="Select today's appointment to check in"
        :breadcrumbs="[['label' => 'Queue', 'url' => route('queue.index')], ['label' => 'Check-in']]">
        <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Queue
        </a>
    </x-page-header>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Today's Appointments</h6>
        </div>
        <div class="card-body p-0">
            @forelse ($appointments as $appointment)
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                        <div class="text-center d-none d-sm-block">
                            <div class="fw-bold fs-5 text-primary">{{ fmt_time($appointment->time) }}</div>
                            <div class="small text-muted">{{ fmt_date($appointment->date) }}</div>
                        </div>
                        <span class="avatar bg-primary">{{ initials($appointment->patient?->name ?? $appointment->name) }}</span>
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ $appointment->patient?->name ?? $appointment->name }}</div>
                            <small class="text-muted">{{ $appointment->patient?->patient_number }}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-none d-md-block">
                            <small class="text-muted d-block">Doctor</small>
                            <span class="fw-medium">{{ $appointment->doctor?->name }}</span>
                        </div>
                        <div class="d-none d-md-block">
                            <small class="text-muted d-block">Appointment</small>
                            <span class="badge bg-primary">{{ $appointment->appointment_number }}</span>
                        </div>
                        <span class="badge {{ $appointment->status->badgeClass() }}">{{ $appointment->status->label() }}</span>
                        @can('queue.checkin')
                            <form method="POST" action="{{ route('queue.checkin') }}">
                                @csrf
                                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                                <button class="btn btn-success btn-sm" onclick="return confirm('Check in this patient?')">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Check In
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5 px-3">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                    <p class="mb-0">No appointments to check in today.</p>
                    <small>All appointments have been checked in or there are no scheduled/confirmed appointments.</small>
                </div>
            @endforelse
        </div>
    </div>
</x-auth-layout>
