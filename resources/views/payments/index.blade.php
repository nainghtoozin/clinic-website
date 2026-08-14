<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Payments</h4>
            <small class="text-muted">{{ $payments->total() }} payments</small>
        </div>
        @can('payment.create')
            <a href="{{ route('payments.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Record Payment</a>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2"><select name="payment_method" class="form-select"><option value="">All Methods</option><option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option><option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option><option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option><option value="mobile_payment" {{ request('payment_method') === 'mobile_payment' ? 'selected' : '' }}>Mobile</option></select></div>
                <div class="col-md-2"><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div>
                <div class="col-md-2"><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div>
                <div class="col-md-3 d-flex gap-2"><button type="submit" class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Search</button><a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Clear</a></div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($payments->isEmpty())
                <div class="text-center text-muted py-5"><i class="bi bi-cash-stack fs-1 d-block mb-2"></i>No payments found.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Invoice #</th><th>Patient</th><th>Amount</th><th>Method</th><th>Reference</th><th>Recorded By</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>{{ fmt_datetime($payment->paid_at) }}</td>
                                    <td><a href="{{ route('invoices.show', $payment->invoice) }}"><span class="badge bg-primary">{{ $payment->invoice->invoice_number }}</span></a></td>
                                    <td>{{ $payment->invoice->patient->name ?? '-' }}</td>
                                    <td class="fw-semibold text-success">${{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->getPaymentMethodLabel() }}</td>
                                    <td>{{ $payment->reference_number ?? '-' }}</td>
                                    <td>{{ $payment->recordedBy->name ?? '-' }}</td>
                                    <td><a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-receipt"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
</x-auth-layout>
