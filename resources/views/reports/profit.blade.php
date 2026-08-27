<x-auth-layout>
    <x-page-header title="Profit Report" subtitle="Revenue vs Expenses analysis"
        :breadcrumbs="[['label' => 'Reports', 'url' => route('reports.index')], ['label' => 'Profit']]">
    </x-page-header>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.profit') }}">
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
                            <a href="{{ route('reports.profit', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Month</a>
                            <a href="{{ route('reports.profit', ['date_from' => now()->subMonth()->startOfMonth()->toDateString(), 'date_to' => now()->subMonth()->endOfMonth()->toDateString()]) }}" class="btn btn-outline-secondary">Last Month</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Total Revenue</div>
                    <div class="fs-3 fw-bold text-success mt-1">{{ number_format($totalRevenue, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Total Expenses</div>
                    <div class="fs-3 fw-bold text-danger mt-1">{{ number_format($totalExpenses, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Net Income</div>
                    <div class="fs-3 fw-bold {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }} mt-1">{{ number_format($netIncome, 2) }}</div>
                    @if ($totalRevenue > 0)
                        <div class="small text-muted">{{ number_format(($netIncome / $totalRevenue) * 100, 1) }}% margin</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Daily Breakdown</h6>
        </div>
        <div class="card-body p-0">
            @if ($dailyData->isEmpty())
                <div class="text-center text-muted py-4">No data</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th class="text-end">Revenue</th><th class="text-end">Expenses</th><th class="text-end">Net Income</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($dailyData as $day)
                                <tr>
                                    <td class="small">{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
                                    <td class="text-end small text-success fw-semibold">{{ number_format($day['revenue'], 2) }}</td>
                                    <td class="text-end small text-danger fw-semibold">{{ number_format($day['expenses'], 2) }}</td>
                                    <td class="text-end small fw-bold {{ $day['net'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($day['net'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold text-success">{{ number_format($totalRevenue, 2) }}</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($totalExpenses, 2) }}</td>
                                <td class="text-end fw-bold {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netIncome, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>
