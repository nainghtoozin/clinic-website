<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Check-in Appointment</h4>
            <small class="text-muted">Select today's appointment to check in</small>
        </div>
        <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Queue
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Today's Appointments</h6>
        </div>
        <div class="card-body p-0">
            @forelse ($appointments as $appointment)
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                    <div class="row flex-grow-1">
                        <div class="col-md-2">
                            <div class="small text-muted">Time</div>
                            <div class="fw-semibold">
                                {{ fmt_time($appointment->time) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Patient</div>
                            <div class="fw-semibold">{{ $appointment->patient->name ?? $appointment->name }}</div>
                            <div class="small text-muted">{{ $appointment->patient?->patient_number }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Doctor</div>
                            <div class="fw-semibold">{{ $appointment->doctor->name }}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="small text-muted">Appointment #</div>
                            <div><span class="badge bg-primary">{{ $appointment->appointment_number }}</span></div>
                        </div>
                        <div class="col-md-2">
                            <div class="small text-muted">Status</div>
                            <div>
                                <span class="badge {{ $appointment->status->badgeClass() }}">
                                    {{ $appointment->status->label() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ms-3">
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
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                    <p class="mb-0">No appointments to check in today.</p>
                    <small>All appointments have been checked in or there are no scheduled/confirmed appointments.</small>
                </div>
            @endforelse
        </div>
    </div>
</x-auth-layout>
