<x-auth-layout>

    @php
        $todaySuffix = $isToday ? ' Today' : '';
        $pageTitle = $isToday ? "Today's Overview" : 'Daily Overview';
        $pageSubtitle = $isToday
            ? "Overview of today's activity at the clinic"
            : "Overview of clinic activity from {$dateFromLabel} to {$dateToLabel}";
        $presets = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
        ];
    @endphp

    <x-page-header :title="$pageTitle" :subtitle="$pageSubtitle"
        :breadcrumbs="[['label' => $pageTitle]]">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" action="{{ route('dashboard') }}" class="d-flex flex-wrap align-items-center gap-2" role="search">
                <div class="btn-group btn-group-sm" role="group" aria-label="Date range presets">
                    @foreach ($presets as $key => $label)
                        <a href="{{ route('dashboard', ['period' => $key]) }}"
                            class="btn {{ $selectedPeriod === $key ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <div class="input-group input-group-sm" style="width:auto;">
                    <span class="input-group-text text-muted">From</span>
                    <input type="date" name="date_from" value="{{ old('date_from', $dateFrom) }}" class="form-control"
                        aria-label="Start date" style="width: 150px;">
                    <span class="input-group-text"><i class="bi bi-arrow-right"></i></span>
                    <span class="input-group-text text-muted">To</span>
                    <input type="date" name="date_to" value="{{ old('date_to', $dateTo) }}" class="form-control"
                        aria-label="End date" style="width: 150px;">
                    <button type="submit" class="btn btn-primary" aria-label="Apply date range"><i class="bi bi-check2"></i></button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" title="Reset to today"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>
            @can('appointment.create')
                <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> New Appointment
                </a>
            @endcan
        </div>
    </x-page-header>

    @error('date_range')
        <div class="alert alert-danger py-2 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ $message }}
        </div>
    @enderror

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Appointments{{ $todaySuffix }}</div>
                            <h4 class="stat-value mb-0">{{ $todayAppointments }}</h4>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="bi bi-calendar-check text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Queue Waiting</div>
                            <h4 class="stat-value mb-0 {{ $queueWaiting > 0 ? 'text-warning' : '' }}">{{ $queueWaiting }}</h4>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10">
                            <i class="bi bi-clock text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Consultations</div>
                            <h4 class="stat-value mb-0">{{ $completedConsultations }}<small class="text-muted fs-6">/{{ $todayConsultations }}</small></h4>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="bi bi-clipboard2-pulse text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Patients{{ $todaySuffix }}</div>
                            <h4 class="stat-value mb-0">{{ $todayNewPatients }}</h4>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10">
                            <i class="bi bi-person-plus text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall metrics -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Active Patients</div>
                            <h5 class="stat-value mb-0">{{ $totalActivePatients }}</h5>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="bi bi-people text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Total Appointments</div>
                            <h5 class="stat-value mb-0">{{ $totalAppointments }}</h5>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="bi bi-calendar2-check text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Available Doctors</div>
                            <h5 class="stat-value mb-0">{{ $availableDoctors }}</h5>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10">
                            <i class="bi bi-person-badge text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Prescriptions{{ $todaySuffix }}</div>
                            <h5 class="stat-value mb-0">{{ $prescriptionsToday }}</h5>
                        </div>
                        <div class="stat-icon bg-secondary bg-opacity-10">
                            <i class="bi bi-file-medical text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('report.financial')
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Invoiced{{ $todaySuffix }}</div>
                            <h5 class="stat-value mb-0">{{ number_format($todayInvoiced, 2) }}</h5>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="bi bi-receipt text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Paid{{ $todaySuffix }}</div>
                            <h5 class="stat-value mb-0 text-success">{{ number_format($todayPaid, 2) }}</h5>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="bi bi-cash-stack text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Outstanding</div>
                            <h5 class="stat-value mb-0 {{ $outstandingBalance > 0 ? 'text-danger' : '' }}">{{ number_format($outstandingBalance, 2) }}</h5>
                            <small class="text-muted">{{ $currentOutstandingInvoices }} unpaid invoice(s)</small>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10">
                            <i class="bi bi-exclamation-circle text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <div class="row g-4 mb-4">
        <!-- Today's Appointments -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-semibold">{{ $isToday ? "Today's Appointments" : 'Appointments' }}</h6>
                    <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayAppointmentsList as $appointment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar avatar-sm bg-primary">{{ initials($appointment->name ?? $appointment->patient->name) }}</span>
                                                <div>
                                                    <div class="fw-medium">{{ $appointment->name ?? $appointment->patient->name ?? '-' }}</div>
                                                    <small class="text-muted">{{ $appointment->appointment_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($appointment->doctor)
                                                    @if ($appointment->doctor->profile_image)
                                                        <img src="{{ Storage::url($appointment->doctor->profile_image) }}" alt="" class="avatar avatar-sm">
                                                    @else
                                                        <span class="avatar avatar-sm bg-info">{{ initials($appointment->doctor->name) }}</span>
                                                    @endif
                                                    <span>{{ $appointment->doctor->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ fmt_time($appointment->time) }}</td>
                                        <td>
                                            <span class="badge {{ $appointment->status->badgeClass() }}">
                                                {{ $appointment->status->label() }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                            {{ $isSingleDay
                                                ? ($isToday ? 'No appointments scheduled for today' : 'No appointments scheduled for ' . $dateFromLabel)
                                                : 'No appointments scheduled in this date range' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Status -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-semibold">Queue</h6>
                    <a href="{{ route('queue.index') }}" class="btn btn-sm btn-outline-primary">Open</a>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-clock text-warning me-2"></i>Waiting</span>
                        <span class="badge bg-warning text-dark">{{ $queueWaiting }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-megaphone text-info me-2"></i>Called</span>
                        <span class="badge bg-info text-dark">{{ $queueCalled }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-clipboard2-pulse text-primary me-2"></i>In Consultation</span>
                        <span class="badge bg-primary">{{ $queueInConsultation }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-check-circle text-success me-2"></i>Completed</span>
                        <span class="badge bg-success">{{ $queueCompleted }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Appointment Status -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold">Appointments by Status</h6>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-info-circle text-info me-2"></i>Scheduled</span>
                        <span class="badge bg-info text-dark">{{ $appointmentsScheduled }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-check-circle text-success me-2"></i>Confirmed</span>
                        <span class="badge bg-success">{{ $appointmentsConfirmed }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-box-arrow-in-right text-warning me-2"></i>Checked In</span>
                        <span class="badge bg-warning text-dark">{{ $appointmentsCheckedIn }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-check2-all text-secondary me-2"></i>Completed</span>
                        <span class="badge bg-secondary">{{ $appointmentsCompleted }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-x-circle text-danger me-2"></i>Cancelled</span>
                        <span class="badge bg-danger">{{ $appointmentsCancelled }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctor Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-semibold">Doctors{{ $todaySuffix }}</h6>
                    <a href="{{ route('doctors.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @forelse($doctorSummary as $doctor)
                        <div class="d-flex align-items-center list-row">
                            <div class="flex-grow-1 d-flex align-items-center gap-2">
                                @if ($doctor->profile_image)
                                    <img src="{{ Storage::url($doctor->profile_image) }}" alt="" class="avatar avatar-sm">
                                @else
                                    <span class="avatar avatar-sm bg-primary">{{ initials($doctor->name) }}</span>
                                @endif
                                <div>
                                    <div class="small fw-medium">{{ $doctor->name }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $doctor->department->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $doctor->today_appointments }}</span>
                                <span class="badge bg-success bg-opacity-10 text-success">{{ $doctor->today_completed }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-person-x fs-3 d-block mb-2"></i>
                            {{ $isSingleDay
                                ? ($isToday ? 'No doctors on duty today' : 'No doctors on duty on ' . $dateFromLabel)
                                : 'No doctors on duty in this date range' }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Inventory Alerts -->
        @can('inventory.view')
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-semibold">Inventory Alerts</h6>
                    <a href="{{ route('inventory.dashboard') }}" class="btn btn-sm btn-outline-primary">View</a>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-capsule text-primary me-2"></i>Active Medicines</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $totalActiveMedicines }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock</span>
                        <span class="badge bg-warning text-dark">{{ $lowStockCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-x-octagon text-danger me-2"></i>Expired</span>
                        <span class="badge bg-danger">{{ $expiredCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center list-row">
                        <span class="small"><i class="bi bi-clock text-info me-2"></i>Expiring Soon</span>
                        <span class="badge bg-info text-dark">{{ $expiringSoonCount }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>

</x-auth-layout>
