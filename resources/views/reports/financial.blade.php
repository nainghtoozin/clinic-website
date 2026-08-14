<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Financial Report</h4>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Invoiced</h6>
                    <h3 class="text-primary">{{ number_format($totalInvoiced, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Paid</h6>
                    <h3 class="text-success">{{ number_format($totalPaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Outstanding</h6>
                    <h3 class="text-danger">{{ number_format($totalOutstanding, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Payment Count</h6>
                    <h3 class="text-info">{{ $paymentCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if($paymentsByMethod->count())
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Payments by Method</strong></div>
        <div class="card-body">
            <div class="row">
                @foreach($paymentsByMethod as $method)
                <div class="col-md-3">
                    <div class="text-center">
                        <h6 class="text-muted">{{ $method->payment_method }}</h6>
                        <h5 class="text-primary">{{ number_format($method->total, 2) }}</h5>
                        <small class="text-muted">{{ $method->count }} payment(s)</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Filters</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.financial') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Invoice Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="mobile_payment" {{ request('payment_method') == 'mobile_payment' ? 'selected' : '' }}>Mobile</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('reports.financial') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Invoices</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td><small class="text-muted">{{ $invoice->invoice_number }}</small></td>
                                <td>{{ $invoice->patient->name ?? '-' }}</td>
                                <td>{{ $invoice->doctor->name ?? '-' }}</td>
                                <td>{{ number_format($invoice->total, 2) }}</td>
                                <td>{{ number_format($invoice->amount_paid, 2) }}</td>
                                <td>{{ number_format($invoice->balance, 2) }}</td>
                                <td>
                                    <span class="badge {{ $invoice->getStatusBadgeClass() }}">
                                        {{ $invoice->getStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No invoices found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer bg-white">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</x-auth-layout>
