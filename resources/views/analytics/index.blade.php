<x-auth-layout>
    <x-page-header title="Business Analytics" subtitle="Comprehensive clinic performance insights"
        :breadcrumbs="[['label' => 'Reports', 'url' => route('reports.index')], ['label' => 'Analytics']]">
        <a href="{{ route('analytics.export', ['type' => 'financial', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-download me-1"></i> Export Financial
        </a>
        <a href="{{ route('analytics.export', ['type' => 'patients', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-download me-1"></i> Export Patients
        </a>
    </x-page-header>

    {{-- DATE RANGE FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('analytics.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Start Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">End Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group btn-group-sm" role="group">
                            @php
                                $today = now()->toDateString();
                                $presets = [
                                    'Today' => [$today, $today],
                                    'This Week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
                                    'This Month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                                    'Last Month' => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
                                    'This Year' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
                                ];
                            @endphp
                            @foreach ($presets as $label => [$df, $dt])
                                <a href="{{ route('analytics.index', ['date_from' => $df, 'date_to' => $dt]) }}" class="btn btn-outline-secondary">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- COMPARISON SUMMARY --}}
    @php
        $cmpIcons = ['up' => 'bi-arrow-up text-success', 'down' => 'bi-arrow-down text-danger', 'same' => 'bi-dash text-muted'];
        $cmpColors = ['up' => 'text-success', 'down' => 'text-danger', 'same' => 'text-muted'];
    @endphp
    <div class="row g-3 mb-4">
        @foreach ([
            ['key' => 'revenue', 'label' => 'Revenue', 'icon' => 'bi-cash-stack', 'bg' => 'success'],
            ['key' => 'expenses', 'label' => 'Expenses', 'icon' => 'bi-receipt', 'bg' => 'danger'],
            ['key' => 'net_income', 'label' => 'Net Income', 'icon' => 'bi-graph-up', 'bg' => 'primary'],
            ['key' => 'patients', 'label' => 'New Patients', 'icon' => 'bi-person-plus', 'bg' => 'info'],
            ['key' => 'appointments', 'label' => 'Appointments', 'icon' => 'bi-calendar-check', 'bg' => 'warning'],
        ] as $item)
            @php $c = $comparison[$item['key']]; @endphp
            <div class="col-6 col-md">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-muted small">{{ $item['label'] }}</div>
                                <div class="fs-5 fw-bold mt-1">{{ number_format($c['current'], 0) }}</div>
                            </div>
                            <div class="bg-{{ $item['bg'] }} bg-opacity-10 p-2 rounded"><i class="bi {{ $item['icon'] }} text-{{ $item['bg'] }}"></i></div>
                        </div>
                        <div class="small mt-2">
                            <i class="bi {{ $cmpIcons[$c['direction']] }}"></i>
                            <span class="{{ $cmpColors[$c['direction']] }}">{{ $c['percentage'] }}%</span>
                            <span class="text-muted">vs previous</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PATIENT ANALYTICS --}}
    <div class="analytics-section mb-4">
        <div class="section-header">
            <i class="bi bi-people"></i> Patient Analytics
        </div>
        <div class="section-body">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Total Patients</div>
                        <div class="data-value text-primary">{{ number_format($totalPatients) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">New Patients</div>
                        <div class="data-value text-success">{{ number_format($newPatients) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Returning Patients</div>
                        <div class="data-value text-info">{{ number_format($returningPatients) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Active Patients</div>
                        <div class="data-value text-warning">{{ number_format($activePatients) }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="analytics-sub-section">
                        <div class="sub-title">By Gender</div>
                        @if ($patientsByGender->isEmpty())
                            <div class="analytics-empty-state">No data</div>
                        @else
                            @foreach ($patientsByGender as $g)
                                @php $pct = $totalPatients > 0 ? round($g->count / $totalPatients * 100, 1) : 0; @endphp
                                <div class="analytics-progress-row">
                                    <div class="progress-label">
                                        <span class="label-text">{{ ucfirst($g->gender ?? 'Unknown') }}</span>
                                        <span><span class="label-value">{{ $g->count }}</span><span class="label-pct">({{ $pct }}%)</span></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="analytics-sub-section">
                        <div class="sub-title">By Age Group</div>
                        @if ($patientsByAgeGroup->isEmpty())
                            <div class="analytics-empty-state">No data</div>
                        @else
                            @foreach ($patientsByAgeGroup as $ag)
                                @php $pct = $totalPatients > 0 ? round($ag->count / $totalPatients * 100, 1) : 0; @endphp
                                <div class="analytics-progress-row">
                                    <div class="progress-label">
                                        <span class="label-text">{{ $ag->age_group }}</span>
                                        <span><span class="label-value">{{ $ag->count }}</span><span class="label-pct">({{ $pct }}%)</span></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="analytics-sub-section">
                        <div class="sub-title">By Blood Group</div>
                        @if ($patientsByBloodGroup->isEmpty())
                            <div class="analytics-empty-state">No data</div>
                        @else
                            @foreach ($patientsByBloodGroup->take(8) as $bg)
                                @php $pct = $totalPatients > 0 ? round($bg->count / $totalPatients * 100, 1) : 0; @endphp
                                <div class="analytics-progress-row">
                                    <div class="progress-label">
                                        <span class="label-text">{{ $bg->blood_group }}</span>
                                        <span><span class="label-value">{{ $bg->count }}</span><span class="label-pct">({{ $pct }}%)</span></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            @if ($patientTrend->isNotEmpty())
                <div class="analytics-sub-section">
                    <div class="sub-title">Registration Trend</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Date</th><th class="text-end">New Registrations</th></tr></thead>
                            <tbody>
                                @foreach ($patientTrend as $t)
                                    <tr><td class="small">{{ $t->date }}</td><td class="text-end small fw-semibold">{{ $t->count }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- APPOINTMENT ANALYTICS --}}
    <div class="analytics-section mb-4">
        <div class="section-header">
            <i class="bi bi-calendar-check"></i> Appointment Analytics
        </div>
        <div class="section-body">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Total Appointments</div>
                        <div class="data-value text-primary">{{ number_format($totalAppointments) }}</div>
                    </div>
                </div>
                @php $statusColors = ['scheduled' => 'info', 'confirmed' => 'success', 'completed' => 'secondary', 'cancelled' => 'danger', 'pending' => 'warning', 'checked_in' => 'primary']; @endphp
                @foreach ($appointmentsByStatus as $s)
                    @php $statusVal = is_string($s->status) ? $s->status : $s->status->value; @endphp
                    <div class="col-6 col-md-3">
                        <div class="analytics-data-card text-center">
                            <div class="data-label">{{ ucfirst(str_replace('_', ' ', $statusVal)) }}</div>
                            <div class="data-value text-{{ $statusColors[$statusVal] ?? 'secondary' }}">{{ $s->count }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="analytics-sub-section">
                        <div class="sub-title">By Department</div>
                        @if ($appointmentsByDepartment->isEmpty())
                            <div class="analytics-empty-state">No data</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>Department</th><th class="text-end">Count</th><th class="text-end">%</th></tr></thead>
                                    <tbody>
                                        @foreach ($appointmentsByDepartment as $d)
                                            @php $pct = $totalAppointments > 0 ? round($d->count / $totalAppointments * 100, 1) : 0; @endphp
                                            <tr>
                                                <td class="small">{{ $d->department_name }}</td>
                                                <td class="text-end small fw-semibold">{{ $d->count }}</td>
                                                <td class="text-end small text-muted">{{ $pct }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="analytics-sub-section">
                        <div class="sub-title">By Doctor (Top 10)</div>
                        @if ($appointmentsByDoctor->isEmpty())
                            <div class="analytics-empty-state">No data</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>Doctor</th><th class="text-end">Total</th><th class="text-end text-success">Completed</th><th class="text-end text-danger">Cancelled</th></tr></thead>
                                    <tbody>
                                        @foreach ($appointmentsByDoctor->take(10) as $doc)
                                            <tr>
                                                <td class="small fw-semibold">{{ $doc->doctor?->name ?? '-' }}</td>
                                                <td class="text-end small">{{ $doc->total }}</td>
                                                <td class="text-end small text-success">{{ $doc->completed }}</td>
                                                <td class="text-end small text-danger">{{ $doc->cancelled }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DOCTOR ANALYTICS --}}
    <div class="analytics-section mb-4">
        <div class="section-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-person-badge"></i> Doctor Performance</span>
            @if($doctorPaginator->lastPage() > 1)
                <span class="text-muted small">{{ $doctorPaginator->firstItem() }}–{{ $doctorPaginator->lastItem() }} of {{ $doctorPaginator->total() }}</span>
            @endif
        </div>
        <div class="section-body p-0">
            @if ($doctorStats->isEmpty())
                <div class="analytics-empty-state">No doctor data</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Doctor</th>
                                <th>Department</th>
                                <th class="text-end">Appointments</th>
                                <th class="text-end">Completed</th>
                                <th class="text-end">Cancelled</th>
                                <th class="text-end">Patients</th>
                                <th class="text-end">Consultations</th>
                                <th class="text-end">Prescriptions</th>
                                <th class="text-end">Investigations</th>
                                @can('report.financial')
                                    <th class="text-end">Revenue</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($doctorStats as $doc)
                                <tr>
                                    <td class="small fw-semibold">{{ $doc->name }}</td>
                                    <td class="small">{{ $doc->department?->name ?? '-' }}</td>
                                    <td class="text-end small">{{ $doc->appointment_count }}</td>
                                    <td class="text-end small text-success">{{ $doc->completed_count }}</td>
                                    <td class="text-end small text-danger">{{ $doc->cancelled_count }}</td>
                                    <td class="text-end small">{{ $doc->patient_count }}</td>
                                    <td class="text-end small">{{ $doc->consultation_count }}</td>
                                    <td class="text-end small">{{ $doc->prescription_count }}</td>
                                    <td class="text-end small">{{ $doc->investigation_count }}</td>
                                    @can('report.financial')
                                        <td class="text-end small fw-semibold">{{ number_format($doc->revenue ?? 0, 2) }}</td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($doctorPaginator->hasPages())
                    <div class="d-flex justify-content-center py-2 border-top">
                        {{ $doctorPaginator->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- CONSULTATION & DIAGNOSIS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="analytics-section h-100 d-flex flex-column">
                <div class="section-header">
                    <i class="bi bi-clipboard2-pulse"></i> Consultation Analytics
                </div>
                <div class="section-body">
                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="analytics-data-card text-center">
                                <div class="data-label">Total</div>
                                <div class="data-value">{{ number_format($totalConsultations) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="analytics-data-card text-center">
                                <div class="data-label">Completed</div>
                                <div class="data-value text-success">{{ number_format($completedConsultations) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="analytics-data-card text-center">
                                <div class="data-label">Draft</div>
                                <div class="data-value text-warning">{{ number_format($draftConsultations) }}</div>
                            </div>
                        </div>
                    </div>
                    @if ($consultationTrend->isNotEmpty())
                        <div class="analytics-sub-section">
                            <div class="sub-title">Trend</div>
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>Date</th><th class="text-end">Count</th></tr></thead>
                                    <tbody>
                                        @foreach ($consultationTrend as $t)
                                            <tr><td class="small">{{ $t->date }}</td><td class="text-end small fw-semibold">{{ $t->count }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-section h-100 d-flex flex-column">
                <div class="section-header">
                    <i class="bi bi-bandaid"></i> Top Diagnoses
                </div>
                <div class="section-body">
                    @if ($topDiagnoses->isEmpty())
                        <div class="analytics-empty-state">No diagnosis data</div>
                    @else
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead><tr><th>Diagnosis</th><th class="text-end">Count</th></tr></thead>
                                <tbody>
                                    @foreach ($topDiagnoses as $d)
                                        <tr>
                                            <td class="small">{{ Str::limit($d->diagnosis, 60) }}</td>
                                            <td class="text-end small fw-semibold">{{ $d->count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- PRESCRIPTION & MEDICINE --}}
    <div class="analytics-section mb-4">
        <div class="section-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-capsule"></i> Prescription & Medicine Analytics</span>
            @if($topMedicinesPaginator->lastPage() > 1)
                <span class="text-muted small">{{ $topMedicinesPaginator->firstItem() }}–{{ $topMedicinesPaginator->lastItem() }} of {{ $topMedicinesPaginator->total() }}</span>
            @endif
        </div>
        <div class="section-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="analytics-data-card">
                        <div class="data-label">Total Prescriptions</div>
                        <div class="data-value text-primary">{{ number_format($totalPrescriptions) }}</div>
                        <div class="data-range mt-1">{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="analytics-data-card">
                        <div class="data-label">Unique Medicines</div>
                        <div class="data-value text-info">{{ $topMedicinesPaginator->total() }}</div>
                        <div class="data-range mt-1">Medicines prescribed in range</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="analytics-data-card">
                        <div class="data-label">Total Quantity</div>
                        <div class="data-value text-success">{{ number_format($topMedicinesPaginator->sum('total_quantity')) }}</div>
                        <div class="data-range mt-1">Units prescribed in range</div>
                    </div>
                </div>
            </div>

            @if ($topMedicinesPaginator->isNotEmpty())
                <div class="analytics-sub-section">
                    <div class="sub-title">Top Prescribed Medicines</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 analytics-medicine-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Medicine</th>
                                    <th class="text-end">Total Qty</th>
                                    <th class="text-end">Prescriptions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topMedicinesPaginator as $idx => $m)
                                    @php $rank = $topMedicinesPaginator->firstItem() + $idx; @endphp
                                    <tr class="{{ $rank <= 3 ? 'analytics-rank-top' : '' }}">
                                        <td>
                                            <span class="analytics-rank-badge {{ $rank <= 3 ? 'analytics-rank-top-' . $rank : '' }}">{{ $rank }}</span>
                                        </td>
                                        <td class="fw-semibold">{{ $m->medicine_name }}</td>
                                        <td class="text-end">{{ number_format($m->total_quantity) }}</td>
                                        <td class="text-end fw-semibold">{{ $m->prescription_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($topMedicinesPaginator->hasPages())
                        <div class="d-flex justify-content-center py-2 border-top mt-2">
                            {{ $topMedicinesPaginator->links() }}
                        </div>
                    @endif
                </div>
            @else
                <div class="analytics-empty-state">
                    <i class="bi bi-capsule d-block mb-2" style="font-size: 1.4rem;"></i>
                    No prescription data for the selected date range
                </div>
            @endif
        </div>
    </div>

    {{-- INVENTORY ANALYTICS --}}
    <div class="analytics-section mb-4">
        <div class="section-header">
            <i class="bi bi-box-seam"></i> Inventory Analytics
        </div>
        <div class="section-body">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Active Medicines</div>
                        <div class="data-value text-primary">{{ number_format($totalMedicines) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Low Stock</div>
                        <div class="data-value text-warning">{{ number_format($lowStockCount) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Out of Stock</div>
                        <div class="data-value text-danger">{{ number_format($outOfStockCount) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Expired</div>
                        <div class="data-value text-danger">{{ number_format($expiredCount) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Expiring Soon</div>
                        <div class="data-value text-warning">{{ number_format($expiringSoonCount) }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="analytics-sub-section">
                        <div class="sub-title">Stock Movements by Type</div>
                        @if ($stockMovementsByType->isEmpty())
                            <div class="analytics-empty-state">
                                <i class="bi bi-arrow-left-right d-block mb-2" style="font-size: 1.4rem;"></i>
                                No movement data
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0 analytics-movement-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th class="text-end">Count</th>
                                            <th class="text-end">Total Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $badgeMap = [
                                                'stock_in' => 'badge-stock-in',
                                                'stock_out' => 'badge-stock-out',
                                                'dispensed' => 'badge-dispensed',
                                                'adjustment' => 'badge-adjustment',
                                                'expired' => 'badge-expired',
                                                'opening' => 'badge-opening',
                                            ];
                                        @endphp
                                        @foreach ($stockMovementsByType as $mv)
                                            @php
                                                $badgeClass = $badgeMap[$mv->type] ?? 'badge-opening';
                                                $label = ucfirst(str_replace('_', ' ', $mv->type));
                                            @endphp
                                            <tr>
                                                <td><span class="stock-movement-badge {{ $badgeClass }}">{{ $label }}</span></td>
                                                <td class="text-end">{{ number_format($mv->count) }}</td>
                                                <td class="text-end fw-semibold">{{ number_format($mv->total_quantity) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="analytics-sub-section">
                        <div class="sub-title">Fast-Moving Medicines (Dispensed)</div>
                        @if ($fastMovingMedicines->isEmpty())
                            <div class="analytics-empty-state">
                                <i class="bi bi-capsule d-block mb-2" style="font-size: 1.4rem;"></i>
                                No dispensing data
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0 analytics-movement-table">
                                    <thead>
                                        <tr>
                                            <th>Medicine</th>
                                            <th class="text-end">Qty Dispensed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fastMovingMedicines as $fm)
                                            <tr>
                                                <td class="fw-semibold">{{ $fm->medicine_name }}</td>
                                                <td class="text-end fw-semibold">{{ number_format($fm->total_moved) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LAB / INVESTIGATION --}}
    <div class="analytics-section mb-4">
        <div class="section-header">
            <i class="bi bi-lungs"></i> Lab / Investigation Analytics
        </div>
        <div class="section-body">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="analytics-data-card text-center">
                        <div class="data-label">Total Investigations</div>
                        <div class="data-value text-primary">{{ number_format($totalInvestigations) }}</div>
                    </div>
                </div>
                @foreach ($investigationsByStatus as $s)
                    @php $invColors = ['requested' => 'info', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'secondary']; @endphp
                    <div class="col-6 col-md-3">
                        <div class="analytics-data-card text-center">
                            <div class="data-label">{{ ucfirst(str_replace('_', ' ', $s->status)) }}</div>
                            <div class="data-value text-{{ $invColors[$s->status] ?? 'secondary' }}">{{ $s->count }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="analytics-sub-section">
                        <div class="sub-title">By Test Type</div>
                        @if ($investigationsByTest->isEmpty())
                            <div class="analytics-empty-state">No data</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>Test</th><th class="text-end">Count</th></tr></thead>
                                    <tbody>
                                        @foreach ($investigationsByTest as $t)
                                            <tr>
                                                <td class="small fw-semibold">{{ $t->test_name }}</td>
                                                <td class="text-end small">{{ $t->count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="analytics-sub-section">
                        <div class="sub-title">Trend</div>
                        @if ($investigationTrend->isEmpty())
                            <div class="analytics-empty-state">No data</div>
                        @else
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>Date</th><th class="text-end">Count</th></tr></thead>
                                    <tbody>
                                        @foreach ($investigationTrend as $t)
                                            <tr><td class="small">{{ $t->date }}</td><td class="text-end small fw-semibold">{{ $t->count }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FINANCIAL ANALYTICS --}}
    @can('report.financial')
        <div class="analytics-section mb-4">
            <div class="section-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cash-stack"></i> Financial Analytics</span>
                <a href="{{ route('analytics.export', ['type' => 'financial', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
            <div class="section-body">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="analytics-data-card">
                            <div class="data-label">Revenue</div>
                            <div class="data-value text-success">{{ number_format($totalRevenue, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="analytics-data-card">
                            <div class="data-label">Expenses</div>
                            <div class="data-value text-danger">{{ number_format($totalExpensesVal, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="analytics-data-card">
                            <div class="data-label">Net Income</div>
                            <div class="data-value {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netIncome, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="analytics-data-card">
                            <div class="data-label">Outstanding</div>
                            <div class="data-value text-warning">{{ number_format($totalOutstanding, 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="analytics-sub-section">
                            <div class="sub-title">Revenue by Source</div>
                            @if ($revenueBySource->isEmpty())
                                <div class="analytics-empty-state">No data</div>
                            @else
                                @foreach ($revenueBySource as $src)
                                    @php $pct = $totalRevenue > 0 ? round($src->total / $totalRevenue * 100, 1) : 0; @endphp
                                    <div class="analytics-progress-row">
                                        <div class="progress-label">
                                            <span class="label-text">{{ ucfirst($src->type) }}</span>
                                            <span><span class="label-value">{{ number_format($src->total, 2) }}</span><span class="label-pct">({{ $pct }}%)</span></span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="analytics-sub-section">
                            <div class="sub-title">Expenses by Category</div>
                            @if ($expensesByCategory->isEmpty())
                                <div class="analytics-empty-state">No data</div>
                            @else
                                @foreach ($expensesByCategory as $cat)
                                    @php $pct = $totalExpensesVal > 0 ? round($cat->total / $totalExpensesVal * 100, 1) : 0; @endphp
                                    <div class="analytics-progress-row">
                                        <div class="progress-label">
                                            <span class="label-text">{{ $cat->category_name }}</span>
                                            <span><span class="label-value">{{ number_format($cat->total, 2) }}</span><span class="label-pct">({{ $pct }}%)</span></span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="analytics-sub-section">
                            <div class="sub-title">Payments by Method</div>
                            @if ($paymentsByMethod->isEmpty())
                                <div class="analytics-empty-state">No data</div>
                            @else
                                @foreach ($paymentsByMethod as $pm)
                                    @php $pct = $totalRevenue > 0 ? round($pm->total / $totalRevenue * 100, 1) : 0; @endphp
                                    <div class="analytics-progress-row">
                                        <div class="progress-label">
                                            <span class="label-text">{{ ucfirst(str_replace('_', ' ', $pm->payment_method)) }}</span>
                                            <span><span class="label-value">{{ number_format($pm->total, 2) }}</span><span class="label-pct">({{ $pct }}%)</span></span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Revenue vs Expenses Trend --}}
                @if ($revenueExpenseTrend->isNotEmpty())
                    <div class="analytics-sub-section">
                        <div class="sub-title">Revenue vs Expenses Trend</div>
                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>Date</th><th class="text-end">Revenue</th><th class="text-end">Expenses</th><th class="text-end">Net</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($revenueExpenseTrend as $day)
                                        <tr>
                                            <td class="small">{{ $day['date'] }}</td>
                                            <td class="text-end small text-success">{{ number_format($day['revenue'], 2) }}</td>
                                            <td class="text-end small text-danger">{{ number_format($day['expenses'], 2) }}</td>
                                            <td class="text-end small fw-semibold {{ $day['net'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($day['net'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endcan
</x-auth-layout>
