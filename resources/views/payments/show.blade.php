<x-auth-layout>
    <x-page-header title="Payment Details" subtitle="{{ $payment->invoice->patient?->name ?? '' }} &middot; {{ fmt_datetime($payment->paid_at) }}"
        :breadcrumbs="[['label' => 'Payments', 'url' => route('payments.index')], ['label' => 'Payment #' . $payment->id]]">
        <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-info btn-sm d-inline-flex align-items-center">
            <i class="bi bi-receipt me-1"></i> Receipt
        </a>
        <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoice
        </a>
    </x-page-header>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-cash me-2"></i>Payment Details</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label text-muted small">Amount</label><div class="fw-bold fs-4 text-success">${{ number_format($payment->amount, 2) }}</div></div>
                        <div class="col-md-6"><label class="form-label text-muted small">Method</label><div>{{ $payment->getPaymentMethodLabel() }}</div></div>
                        <div class="col-md-6"><label class="form-label text-muted small">Paid At</label><div>{{ fmt_datetime($payment->paid_at) }}</div></div>
                        <div class="col-md-6"><label class="form-label text-muted small">Reference</label><div>{{ $payment->reference_number ?? '-' }}</div></div>
                        <div class="col-md-6"><label class="form-label text-muted small">Recorded By</label><div>{{ $payment->recordedBy->name ?? '-' }}</div></div>
                        @if ($payment->notes)<div class="col-12"><label class="form-label text-muted small">Notes</label><div class="border rounded p-3 bg-light">{{ $payment->notes }}</div></div>@endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoice Summary</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Invoice #</span><a href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Patient</span><span>{{ $payment->invoice->patient->name ?? '-' }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total</span><span>${{ number_format($payment->invoice->total, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Amount Paid</span><span class="text-success">${{ number_format($payment->invoice->amount_paid, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span class="fw-bold">Balance</span><span class="fw-bold {{ $payment->invoice->balance > 0 ? 'text-danger' : 'text-success' }}">${{ number_format($payment->invoice->balance, 2) }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
