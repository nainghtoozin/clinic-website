<x-auth-layout>
    <x-page-header title="Expense Report" subtitle="Expense tracking by category and date"
        :breadcrumbs="[['label' => 'Reports', 'url' => route('reports.index')], ['label' => 'Expenses']]">
    </x-page-header>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.expense') }}">
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
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('reports.expense', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">Today</a>
                            <a href="{{ route('reports.expense', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Month</a>
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
                    <div class="text-muted small">Total Expenses</div>
                    <div class="fs-3 fw-bold text-danger mt-1">{{ number_format($totalExpenses, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Categories Used</div>
                    <div class="fs-3 fw-bold text-primary mt-1">{{ $byCategory->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Average per Expense</div>
                    <div class="fs-3 fw-bold text-info mt-1">{{ $expenses->total() > 0 ? number_format($totalExpenses / $expenses->total(), 2) : '0.00' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($byCategory->isNotEmpty())
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-tags me-2"></i>By Category</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Category</th><th class="text-end">Amount</th><th class="text-end">Count</th><th class="text-end">% of Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($byCategory as $cat)
                                <tr>
                                    <td class="fw-semibold">{{ $cat->category_name }}</td>
                                    <td class="text-end">{{ number_format($cat->total, 2) }}</td>
                                    <td class="text-end text-muted">{{ $cat->count }}</td>
                                    <td class="text-end">{{ $totalExpenses > 0 ? number_format(($cat->total / $totalExpenses) * 100, 1) : '0' }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Expense List</h6>
        </div>
        <div class="card-body p-0">
            @if ($expenses->isEmpty())
                <div class="text-center text-muted py-4">No expenses found</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Ref</th><th>Category</th><th>Description</th><th class="text-end">Amount</th><th>Method</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $exp)
                                <tr>
                                    <td class="small">{{ $exp->expense_date->format('d M Y') }}</td>
                                    <td class="small fw-semibold">{{ $exp->expense_number }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $exp->expenseCategory->name ?? '-' }}</span></td>
                                    <td class="small text-muted">{{ Str::limit($exp->description, 40) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($exp->amount, 2) }}</td>
                                    <td><span class="badge {{ $exp->getPaymentMethodBadgeClass() }}">{{ $exp->payment_method_label }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($expenses->hasPages())
        <div class="d-flex justify-content-center mt-3">{{ $expenses->withQueryString()->links() }}</div>
    @endif
</x-auth-layout>
