<x-auth-layout>
    <x-page-header title="Financial Report" subtitle="Revenue, expenses, and profit analysis"
        :breadcrumbs="[['label' => 'Reports', 'url' => route('reports.index')], ['label' => 'Financial']]">
        <a href="{{ route('reports.financial.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </x-page-header>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.financial') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Start Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">End Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued</option>
                            <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('reports.financial', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">Today</a>
                            <a href="{{ route('reports.financial', ['date_from' => now()->startOfWeek()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Week</a>
                            <a href="{{ route('reports.financial', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Month</a>
                            <a href="{{ route('reports.financial', ['date_from' => now()->subMonth()->startOfMonth()->toDateString(), 'date_to' => now()->subMonth()->endOfMonth()->toDateString()]) }}" class="btn btn-outline-secondary">Last Month</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Total Revenue</div>
                            <div class="fs-4 fw-bold text-success mt-1">{{ number_format($totalPayments, 2) }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded"><i class="bi bi-cash-stack text-success"></i></div>
                    </div>
                    <div class="small text-muted mt-2">{{ number_format($paymentCount) }} payment(s)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Total Expenses</div>
                            <div class="fs-4 fw-bold text-danger mt-1">{{ number_format($totalExpenses, 2) }}</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-2 rounded"><i class="bi bi-receipt text-danger"></i></div>
                    </div>
                    <div class="small text-muted mt-2">{{ number_format($expenseCount) }} expense(s)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Net Income</div>
                            <div class="fs-4 fw-bold {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }} mt-1">{{ number_format($netIncome, 2) }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded"><i class="bi bi-graph-up text-primary"></i></div>
                    </div>
                    <div class="small text-muted mt-2">Revenue - Expenses</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Outstanding</div>
                            <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($totalOutstanding, 2) }}</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded"><i class="bi bi-hourglass-split text-warning"></i></div>
                    </div>
                    <div class="small text-muted mt-2">{{ number_format($cancelledTotal, 2) }} cancelled</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Revenue by Source</h6>
                </div>
                <div class="card-body">
                    @if ($revenueBySource->isEmpty())
                        <div class="text-center text-muted py-3">No revenue data</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Source</th><th class="text-end">Amount</th><th class="text-end">Count</th></tr></thead>
                                <tbody>
                                    @foreach ($revenueBySource as $source)
                                        <tr>
                                            <td><span class="badge bg-light text-dark">{{ ucfirst($source->type) }}</span></td>
                                            <td class="text-end fw-semibold">{{ number_format($source->total, 2) }}</td>
                                            <td class="text-end text-muted">{{ $source->count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td class="fw-bold">Total</td>
                                        <td class="text-end fw-bold">{{ number_format($revenueBySource->sum('total'), 2) }}</td>
                                        <td class="text-end">{{ $revenueBySource->sum('count') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-tags me-2"></i>Expenses by Category</h6>
                </div>
                <div class="card-body">
                    @if ($expensesByCategory->isEmpty())
                        <div class="text-center text-muted py-3">No expenses in this period</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Category</th><th class="text-end">Amount</th><th class="text-end">Count</th></tr></thead>
                                <tbody>
                                    @foreach ($expensesByCategory as $cat)
                                        <tr>
                                            <td><span class="badge bg-light text-dark">{{ $cat->category_name }}</span></td>
                                            <td class="text-end fw-semibold">{{ number_format($cat->total, 2) }}</td>
                                            <td class="text-end text-muted">{{ $cat->count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td class="fw-bold">Total</td>
                                        <td class="text-end fw-bold">{{ number_format($expensesByCategory->sum('total'), 2) }}</td>
                                        <td class="text-end">{{ $expensesByCategory->sum('count') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($paymentsByMethod->isNotEmpty())
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payments by Method</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($paymentsByMethod as $method)
                        <div class="col-6 col-md-3">
                            <div class="text-center">
                                <div class="fs-5 fw-bold">{{ number_format($method->total, 2) }}</div>
                                <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $method->payment_method)) }}</div>
                                <div class="small text-muted">{{ $method->count }} payment(s)</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Invoice List</h6>
        </div>
        <div class="card-body p-0">
            @if ($invoices->isEmpty())
                <div class="text-center text-muted py-4">No invoices found</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $inv)
                                <tr>
                                    <td class="small fw-semibold">{{ $inv->invoice_number }}</td>
                                    <td class="small">{{ $inv->patient->name ?? '-' }}</td>
                                    <td class="small">{{ $inv->created_at->format('d M Y') }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($inv->status) {
                                                'paid' => 'bg-success',
                                                'partially_paid' => 'bg-warning text-dark',
                                                'cancelled' => 'bg-danger',
                                                'issued' => 'bg-info',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $inv->status)) }}</span>
                                    </td>
                                    <td class="text-end small">{{ number_format($inv->total, 2) }}</td>
                                    <td class="text-end small text-success">{{ number_format($inv->amount_paid, 2) }}</td>
                                    <td class="text-end small {{ $inv->balance > 0 ? 'text-danger' : '' }}">{{ number_format($inv->balance, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($invoices->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $invoices->withQueryString()->links() }}
        </div>
    @endif
</x-auth-layout>
