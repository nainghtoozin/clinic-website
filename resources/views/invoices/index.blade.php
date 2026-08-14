<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Invoices</h5>
            <small class="text-muted">{{ $invoices->total() }} invoices</small>
        </div>
        @can('invoice.create')
            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> New Invoice
            </a>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search invoice # or patient..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Filter</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($invoices->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                    <div>No invoices found.</div>
                    @can('invoice.create')
                        <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-plus-circle me-1"></i> Create Invoice
                        </a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Patient</th>
                                <th class="d-none d-md-table-cell">Doctor</th>
                                <th>Date</th>
                                <th class="text-end">Total</th>
                                <th class="text-end d-none d-lg-table-cell">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $invoice->invoice_number }}</span></td>
                                    <td>{{ $invoice->patient->name ?? '-' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $invoice->doctor->name ?? '-' }}</td>
                                    <td>{{ fmt_datetime($invoice->created_at) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($invoice->total, 2) }}</td>
                                    <td class="text-end d-none d-lg-table-cell">${{ number_format($invoice->amount_paid, 2) }}</td>
                                    <td class="text-end">
                                        @if ($invoice->balance > 0)
                                            <span class="text-danger fw-semibold">${{ number_format($invoice->balance, 2) }}</span>
                                        @else
                                            <span class="text-success">$0.00</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $invoice->getStatusBadgeClass() }}">{{ $invoice->getStatusLabel() }}</span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('invoice.edit')
                                                @if ($invoice->isDraft() || $invoice->isIssued() || $invoice->isPartiallyPaid())
                                                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('payment.create')
                                                @if ($invoice->canReceivePayment())
                                                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-outline-success" title="Record Payment">
                                                        <i class="bi bi-cash"></i>
                                                    </a>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>
</x-auth-layout>
