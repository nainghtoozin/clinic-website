<x-auth-layout>
    <x-page-header title="Reports" subtitle="Business intelligence and analytics"
        :breadcrumbs="[['label' => 'Reports']]">
    </x-page-header>

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
                        <i class="bi bi-cash-stack fs-1 text-success mb-3"></i>
                        <h5 class="card-title">Financial Report</h5>
                        <p class="text-muted small">Revenue, expenses, net income, outstanding, by source</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        @can('report.financial')
        <div class="col-md-4">
            <a href="{{ route('reports.expense') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-receipt fs-1 text-danger mb-3"></i>
                        <h5 class="card-title">Expense Report</h5>
                        <p class="text-muted small">Expenses by category, date range, payment method</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        @can('report.financial')
        <div class="col-md-4">
            <a href="{{ route('reports.profit') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-graph-up fs-1 text-primary mb-3"></i>
                        <h5 class="card-title">Profit Report</h5>
                        <p class="text-muted small">Revenue vs Expenses, net income, daily breakdown</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        @can('report.financial')
        <div class="col-md-4">
            <a href="{{ route('reports.payment-method') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-credit-card fs-1 text-warning mb-3"></i>
                        <h5 class="card-title">Payment Methods</h5>
                        <p class="text-muted small">Payments by method, totals, percentages</p>
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
