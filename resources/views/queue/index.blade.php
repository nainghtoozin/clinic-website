<x-auth-layout>
    <x-page-header title="Queue Management" subtitle="Live queue for {{ fmt_today() }}"
        :breadcrumbs="[['label' => 'Queue']]">
        @can('queue.call')
            <form method="POST" action="{{ route('queue.call-next') }}" class="d-inline">
                @csrf
                <input type="hidden" name="doctor_id" value="{{ request('doctor_id') }}">
                <button class="btn btn-warning btn-sm d-inline-flex align-items-center"
                    onclick="return confirm('Call the next waiting patient?')">
                    <i class="bi bi-megaphone me-1"></i> Call Next
                </button>
            </form>
        @endcan
        @can('queue.checkin')
            <a href="{{ route('queue.walkin.form') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-person-plus me-1"></i> Walk-in
            </a>
            <a href="{{ route('queue.checkin.form') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-box-arrow-in-right me-1"></i> Check-in
            </a>
        @endcan
    </x-page-header>

    {{-- Doctor filter --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('queue.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-1">Doctor</label>
                    <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Doctors</option>
                        @foreach ($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                {{ $doc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto col-md-2 d-flex gap-2">
                    <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
                    @if (request()->filled('doctor_id'))
                        <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Summary chips --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body py-2 d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark rounded-pill p-2"><i class="bi bi-clock"></i></span>
                    <div>
                        <div class="stat-label text-muted">Waiting</div>
                        <h5 class="mb-0">{{ $counts['waiting'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body py-2 d-flex align-items-center gap-2">
                    <span class="badge bg-info text-dark rounded-pill p-2"><i class="bi bi-megaphone"></i></span>
                    <div>
                        <div class="stat-label text-muted">Called</div>
                        <h5 class="mb-0">{{ $counts['called'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body py-2 d-flex align-items-center gap-2">
                    <span class="badge bg-success rounded-pill p-2"><i class="bi bi-clipboard2-pulse"></i></span>
                    <div>
                        <div class="stat-label text-muted">In Consultation</div>
                        <h5 class="mb-0">{{ $counts['in_consultation'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body py-2 d-flex align-items-center gap-2">
                    <span class="badge bg-secondary rounded-pill p-2"><i class="bi bi-check2-circle"></i></span>
                    <div>
                        <div class="stat-label text-muted">Done Today</div>
                        <h5 class="mb-0">{{ $counts['completed'] + $counts['cancelled'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Now serving / next up --}}
    @php
        $serving = collect($tickets->get('in_consultation', []))->first();
        $calledTicket = collect($tickets->get('called', []))->first();
        $nextUp = collect($tickets->get('waiting', []))->first();
    @endphp

    @if ($serving)
        <div class="card shadow-sm border-0 mb-3 border-start border-4 border-success">
            <div class="card-body py-3 d-flex align-items-center flex-wrap gap-3">
                <span class="badge bg-success rounded-pill p-2"><i class="bi bi-chat-dots fs-6"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <small class="text-muted text-uppercase fw-semibold d-block">In Consultation</small>
                    <div class="fw-semibold">
                        <span class="badge bg-success me-1">{{ $serving->ticket_number }}</span>
                        {{ $serving->patient?->name ?? 'Patient' }} &middot; {{ $serving->doctor?->name }}
                    </div>
                </div>
                @can('consultation.create')
                    <a href="{{ route('consultations.create', ['queue_ticket_id' => $serving->id]) }}"
                        class="btn btn-sm btn-success">
                        <i class="bi bi-clipboard2-pulse me-1"></i> Open Consultation
                    </a>
                @endcan
            </div>
        </div>
    @elseif ($calledTicket)
        <div class="card shadow-sm border-0 mb-3 border-start border-4 border-info">
            <div class="card-body py-3 d-flex align-items-center flex-wrap gap-3">
                <span class="badge bg-info text-dark rounded-pill p-2"><i class="bi bi-megaphone fs-6"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <small class="text-muted text-uppercase fw-semibold d-block">Called - Ready for Consultation</small>
                    <div class="fw-semibold">
                        <span class="badge bg-info text-dark me-1">{{ $calledTicket->ticket_number }}</span>
                        {{ $calledTicket->patient?->name ?? 'Patient' }} &middot; {{ $calledTicket->doctor?->name }}
                    </div>
                </div>
                @can('queue.consult')
                    <form method="POST" action="{{ route('queue.start-consultation', $calledTicket) }}">
                        @csrf
                        <button class="btn btn-sm btn-primary" onclick="return confirm('Start consultation?')">
                            <i class="bi bi-play-fill me-1"></i> Start Consultation
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    @elseif ($nextUp)
        <div class="card shadow-sm border-0 mb-3 border-start border-4 border-warning">
            <div class="card-body py-3 d-flex align-items-center flex-wrap gap-3">
                <span class="badge bg-warning text-dark rounded-pill p-2"><i class="bi bi-person-lines-fill fs-6"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <small class="text-muted text-uppercase fw-semibold d-block">Next Up</small>
                    <div class="fw-semibold">
                        <span class="badge bg-warning text-dark me-1">{{ $nextUp->ticket_number }}</span>
                        {{ $nextUp->patient?->name ?? 'Patient' }} &middot; {{ $nextUp->doctor?->name }}
                    </div>
                </div>
                @can('queue.call')
                    <form method="POST" action="{{ route('queue.call-ticket', $nextUp) }}">
                        @csrf
                        <button class="btn btn-sm btn-warning" onclick="return confirm('Call {{ $nextUp->patient?->name }}?')">
                            <i class="bi bi-megaphone me-1"></i> Call Now
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    @endif

    {{-- Queue columns --}}
    <div class="row g-3">
        {{-- WAITING --}}
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-2 d-flex align-items-center border-bottom border-warning border-3">
                    <i class="bi bi-clock text-warning me-2"></i>
                    <h6 class="mb-0 fw-semibold">Waiting</h6>
                    <span class="badge bg-warning text-dark ms-auto">{{ $counts['waiting'] }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                    @forelse ($tickets['waiting'] ?? [] as $ticket)
                        <div class="d-flex justify-content-between align-items-start p-3 border-bottom {{ $ticket->position === 1 ? 'bg-warning-subtle' : '' }}">
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-primary" style="font-size:0.8rem;">{{ $ticket->ticket_number }}</span>
                                    @if ($ticket->position === 1)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-person-fill me-1"></i>Next</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Position #{{ $ticket->position }}</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="avatar avatar-sm bg-primary">{{ initials($ticket->patient?->name) }}</span>
                                    <strong class="small text-truncate">{{ $ticket->patient?->name ?? 'Patient' }}</strong>
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-person-badge me-1"></i>{{ $ticket->doctor?->name ?? '-' }}
                                </div>
                                @if ($ticket->appointment)
                                    <div class="small text-muted">
                                        <i class="bi bi-calendar-check me-1"></i>{{ $ticket->appointment->appointment_number }} &middot; {{ fmt_time($ticket->appointment->time) }}
                                    </div>
                                @else
                                    <div class="small text-muted"><i class="bi bi-person-plus me-1"></i>Walk-in</div>
                                @endif
                                <div class="small text-muted">
                                    <i class="bi bi-clock-history me-1"></i>In since {{ fmt_datetime($ticket->checked_in_at) }}
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1 ms-2">
                                @can('queue.call')
                                    <form method="POST" action="{{ route('queue.call-ticket', $ticket) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-warning" onclick="return confirm('Call {{ $ticket->patient?->name }}?')" title="Call">
                                            <i class="bi bi-megaphone"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('queue.cancel')
                                    <form method="POST" action="{{ route('queue.cancel-ticket', $ticket) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this ticket?')" title="Cancel">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 px-3">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                            <span class="small">No patients waiting</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- CALLED --}}
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-2 d-flex align-items-center border-bottom border-info border-3">
                    <i class="bi bi-megaphone text-info me-2"></i>
                    <h6 class="mb-0 fw-semibold">Called</h6>
                    <span class="badge bg-info text-dark ms-auto">{{ $counts['called'] }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                    @forelse ($tickets['called'] ?? [] as $ticket)
                        <div class="d-flex justify-content-between align-items-start p-3 border-bottom bg-info-subtle">
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-info text-dark" style="font-size:0.8rem;">{{ $ticket->ticket_number }}</span>
                                    <span class="badge bg-info text-dark"><i class="bi bi-megaphone me-1"></i>Called</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="avatar avatar-sm bg-info">{{ initials($ticket->patient?->name) }}</span>
                                    <strong class="small text-truncate">{{ $ticket->patient?->name ?? 'Patient' }}</strong>
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-person-badge me-1"></i>{{ $ticket->doctor?->name ?? '-' }}
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-bell me-1"></i>Called at {{ fmt_datetime($ticket->called_at) }}
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1 ms-2">
                                @can('queue.consult')
                                    <form method="POST" action="{{ route('queue.start-consultation', $ticket) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Start consultation with {{ $ticket->patient?->name }}?')">
                                            <i class="bi bi-chat-dots me-1"></i> Start
                                        </button>
                                    </form>
                                @endcan
                                @can('queue.cancel')
                                    <form method="POST" action="{{ route('queue.cancel-ticket', $ticket) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this ticket?')" title="Cancel">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 px-3">
                            <i class="bi bi-megaphone fs-1 d-block mb-2"></i>
                            <span class="small">No patients called</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- IN CONSULTATION --}}
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-2 d-flex align-items-center border-bottom border-success border-3">
                    <i class="bi bi-clipboard2-pulse text-success me-2"></i>
                    <h6 class="mb-0 fw-semibold">In Consultation</h6>
                    <span class="badge bg-success ms-auto">{{ $counts['in_consultation'] }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                    @forelse ($tickets['in_consultation'] ?? [] as $ticket)
                        <div class="d-flex justify-content-between align-items-start p-3 border-bottom bg-success-subtle">
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-success" style="font-size:0.8rem;">{{ $ticket->ticket_number }}</span>
                                    <span class="badge bg-success"><i class="bi bi-chat-dots me-1"></i>Active</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="avatar avatar-sm bg-success">{{ initials($ticket->patient?->name) }}</span>
                                    <strong class="small text-truncate">{{ $ticket->patient?->name ?? 'Patient' }}</strong>
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-person-badge me-1"></i>{{ $ticket->doctor?->name ?? '-' }}
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-clock me-1"></i>Started {{ fmt_datetime($ticket->consultation_started_at) }}
                                </div>
                            </div>
                            <div class="ms-2">
                                @can('consultation.create')
                                    <a href="{{ route('consultations.create', ['queue_ticket_id' => $ticket->id]) }}"
                                        class="btn btn-sm btn-success">
                                        <i class="bi bi-clipboard2-pulse me-1"></i> Open
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 px-3">
                            <i class="bi bi-clipboard-check fs-1 d-block mb-2"></i>
                            <span class="small">No active consultations</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if (($counts['completed'] + $counts['cancelled']) > 0)
        <div class="text-center text-muted small mt-3">
            <i class="bi bi-info-circle me-1"></i>
            {{ $counts['completed'] }} completed &middot; {{ $counts['cancelled'] }} cancelled today.
        </div>
    @endif
</x-auth-layout>
