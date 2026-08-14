<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Reports</h4>
    </div>

    <div class="row g-4">
        @can('report.patient')
        <div class="col-md-4">
            <a href="{{ route('reports.patient') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-person-lines-fill fs-1 text-primary mb-3"></i>
                        <h5 class="card-title">Patient Report</h5>
                        <p class="text-muted small">New patients, patient list, filters by date/status/gender</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        @can('report.appointment')
        <div class="col-md-4">
            <a href="{{ route('reports.appointment') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-calendar-check fs-1 text-success mb-3"></i>
                        <h5 class="card-title">Appointment Report</h5>
                        <p class="text-muted small">Appointments by date range, status, doctor</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        @can('report.consultation')
        <div class="col-md-4">
            <a href="{{ route('reports.consultation') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-clipboard2-pulse fs-1 text-info mb-3"></i>
                        <h5 class="card-title">Consultation Report</h5>
                        <p class="text-muted small">Consultations by date, doctor, status</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        @can('report.financial')
        <div class="col-md-4">
            <a href="{{ route('reports.financial') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-cash-stack fs-1 text-warning mb-3"></i>
                        <h5 class="card-title">Financial Report</h5>
                        <p class="text-muted small">Invoiced, paid, outstanding, payment methods</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        @can('report.inventory')
        <div class="col-md-4">
            <a href="{{ route('reports.inventory') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-box-seam fs-1 text-danger mb-3"></i>
                        <h5 class="card-title">Inventory Report</h5>
                        <p class="text-muted small">Stock levels, low/expired/expiring medicines</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan
    </div>
</x-auth-layout>
