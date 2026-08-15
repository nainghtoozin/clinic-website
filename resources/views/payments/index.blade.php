<x-auth-layout>
    <x-page-header title="Payments" subtitle="Payment history across invoices"
        :breadcrumbs="[['label' => 'Payments']]">
        @can('payment.create')
            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-circle me-1"></i> Record Payment
            </a>
        @endcan
    </x-page-header>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('payments.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="mobile_payment" {{ request('payment_method') === 'mobile_payment' ? 'selected' : '' }}>Mobile Payment</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto col-md-3 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['payment_method', 'date_from', 'date_to']))
                            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results summary --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-cash-stack me-1"></i>{{ $payments->total() }} payment(s)
            @php
                $totalCollected = $payments->sum('amount');
            @endphp
            &middot; ${{ number_format($totalCollected, 2) }} collected
        </small>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Patient</th>
                        <th class="text-end">Amount</th>
                        <th>Method</th>
                        <th class="d-none d-lg-table-cell">Reference</th>
                        <th class="d-none d-md-table-cell">Recorded By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ fmt_datetime($payment->paid_at) }}</td>
                            <td>
                                <a href="{{ route('invoices.show', $payment->invoice) }}">
                                    <span class="badge bg-primary">{{ $payment->invoice->invoice_number }}</span>
                                </a>
                            </td>
                            <td>{{ $payment->invoice->patient?->name ?? '-' }}</td>
                            <td class="text-end fw-semibold text-success">${{ number_format($payment->amount, 2) }}</td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $payment->getPaymentMethodLabel() }}</span></td>
                            <td class="d-none d-lg-table-cell">{{ $payment->reference_number ?? '-' }}</td>
                            <td class="d-none d-md-table-cell">{{ $payment->recordedBy?->name ?? '-' }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-outline-info" title="Receipt">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No payments found</div>
                                <small>Try adjusting your filters.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($payments->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $payments->firstItem() }}&ndash;{{ $payments->lastItem() }} of {{ $payments->total() }}
            </small>
            <div>{{ $payments->links() }}</div>
        </div>
    @endif
</x-auth-layout>
