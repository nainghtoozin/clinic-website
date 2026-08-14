<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Queue Management</h5>
        </div>
        <div class="d-flex gap-2">
            @can('queue.call')
                <form method="POST" action="{{ route('queue.call-next') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="doctor_id" value="{{ request('doctor_id') }}">
                    <button class="btn btn-warning btn-sm" onclick="return confirm('Call the next waiting patient?')">
                        <i class="bi bi-megaphone me-1"></i> Call Next
                    </button>
                </form>
            @endcan
            @can('queue.checkin')
                <a href="{{ route('queue.walkin.form') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-person-plus me-1"></i> Walk-in
                </a>
                <a href="{{ route('queue.checkin.form') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Check-in
                </a>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('queue.index') }}" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="form-label small text-muted mb-0">Doctor:</label>
                </div>
                <div class="col-md-3 col-sm-6">
                    <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Doctors</option>
                        @foreach ($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                {{ $doc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <a href="{{ route('queue.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <!-- WAITING -->
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-2 d-flex align-items-center border-bottom border-warning border-3">
                    <i class="bi bi-clock text-warning me-2"></i>
                    <h6 class="mb-0 fw-semibold">Waiting</h6>
                    <span class="badge bg-warning text-dark ms-auto">{{ ($tickets['waiting'] ?? collect())->count() }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    @forelse ($tickets['waiting'] ?? [] as $ticket)
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-primary me-2" style="font-size:0.8rem;">{{ $ticket->ticket_number }}</span>
                                    <strong class="small">{{ $ticket->patient->name }}</strong>
                                </div>
                                <div class="small text-muted">{{ $ticket->doctor->name }}</div>
                                @if ($ticket->appointment)
                                    <div class="small text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ fmt_time($ticket->appointment->time) }}
                                    </div>
                                @endif
                                <div class="small text-muted">
                                    <i class="bi bi-clock-history me-1"></i>In since {{ fmt_datetime($ticket->checked_in_at) }}
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1 ms-2">
                                @can('queue.call')
                                    <form method="POST" action="{{ route('queue.call-ticket', $ticket) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-warning" onclick="return confirm('Call {{ $ticket->patient->name }}?')">
                                            <i class="bi bi-megaphone"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('queue.cancel')
                                    <form method="POST" action="{{ route('queue.cancel-ticket', $ticket) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this ticket?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                            <span class="small">No patients waiting</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- CALLED -->
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-2 d-flex align-items-center border-bottom border-info border-3">
                    <i class="bi bi-megaphone text-info me-2"></i>
                    <h6 class="mb-0 fw-semibold">Called</h6>
                    <span class="badge bg-info text-dark ms-auto">{{ ($tickets['called'] ?? collect())->count() }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    @forelse ($tickets['called'] ?? [] as $ticket)
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-info me-2" style="font-size:0.8rem;">{{ $ticket->ticket_number }}</span>
                                    <strong class="small">{{ $ticket->patient->name }}</strong>
                                </div>
                                <div class="small text-muted">{{ $ticket->doctor->name }}</div>
                                <div class="small text-muted">
                                    <i class="bi bi-clock me-1"></i>Called at {{ fmt_datetime($ticket->called_at) }}
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1 ms-2">
                                @can('queue.consult')
                                    <form method="POST" action="{{ route('queue.start-consultation', $ticket) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Start consultation with {{ $ticket->patient->name }}?')">
                                            <i class="bi bi-chat-dots me-1"></i> Start
                                        </button>
                                    </form>
                                @endcan
                                @can('queue.cancel')
                                    <form method="POST" action="{{ route('queue.cancel-ticket', $ticket) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this ticket?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-megaphone fs-1 d-block mb-2"></i>
                            <span class="small">No patients called</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- IN CONSULTATION -->
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-2 d-flex align-items-center border-bottom border-success border-3">
                    <i class="bi bi-clipboard2-pulse text-success me-2"></i>
                    <h6 class="mb-0 fw-semibold">In Consultation</h6>
                    <span class="badge bg-success ms-auto">{{ ($tickets['in_consultation'] ?? collect())->count() }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    @forelse ($tickets['in_consultation'] ?? [] as $ticket)
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-success me-2" style="font-size:0.8rem;">{{ $ticket->ticket_number }}</span>
                                    <strong class="small">{{ $ticket->patient->name }}</strong>
                                </div>
                                <div class="small text-muted">{{ $ticket->doctor->name }}</div>
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
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-check fs-1 d-block mb-2"></i>
                            <span class="small">No active consultations</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
