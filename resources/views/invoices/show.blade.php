<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Invoice {{ $invoice->invoice_number }}</h5>
            <small class="text-muted">{{ fmt_datetime($invoice->created_at) }}</small>
        </div>
        <div class="d-flex gap-2">
            @if ($invoice->isDraft())
                @can('invoice.edit')
                    <form method="POST" action="{{ route('invoices.issue', $invoice) }}">
                        @csrf
                        <button class="btn btn-success btn-sm" onclick="return confirm('Issue this invoice?')">
                            <i class="bi bi-send me-1"></i> Issue Invoice
                        </button>
                    </form>
                @endcan
            @endif
            @if ($invoice->canReceivePayment())
                @can('payment.create')
                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-cash me-1"></i> Record Payment
                    </a>
                @endcan
            @endif
            @can('payment.view')
                @if ($invoice->payments->isNotEmpty())
                    <a href="{{ route('payments.receipt', $invoice->payments->latest()->first()) }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-receipt me-1"></i> Receipt
                    </a>
                @endif
            @endcan
            @if ($invoice->isDraft() || $invoice->isIssued() || $invoice->isPartiallyPaid())
                @can('invoice.cancel')
                    <form method="POST" action="{{ route('invoices.cancel', $invoice) }}">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this invoice?')">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </button>
                    </form>
                @endcan
            @endif
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoice Details</h6>
                    <span class="badge {{ $invoice->getStatusBadgeClass() }}">{{ $invoice->getStatusLabel() }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Patient</label>
                            <div class="fw-semibold"><a href="{{ route('patients.show', $invoice->patient) }}">{{ $invoice->patient->name ?? '-' }}</a></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Doctor</label>
                            <div>{{ $invoice->doctor->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Invoice #</label>
                            <div><span class="badge bg-primary">{{ $invoice->invoice_number }}</span></div>
                        </div>
                        @if ($invoice->consultation)
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Consultation</label>
                                <div><a href="{{ route('consultations.show', $invoice->consultation) }}">View</a></div>
                            </div>
                        @endif
                        @if ($invoice->appointment)
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Appointment</label>
                                <div><span class="badge bg-info text-dark">{{ $invoice->appointment->appointment_number }}</span></div>
                            </div>
                        @endif
                        @if ($invoice->issued_at)
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Issued At</label>
                                <div>{{ fmt_datetime($invoice->issued_at) }}</div>
                            </div>
                        @endif
                        @if ($invoice->notes)
                            <div class="col-12">
                                <label class="form-label text-muted small">Notes</label>
                                <div class="border rounded p-3 bg-light">{{ $invoice->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Invoice Items</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Description</th><th>Type</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($invoice->items as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->description }}</td>
                                        <td><span class="badge bg-secondary">{{ $item->getTypeLabel() }}</span></td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No items</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Summary</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Subtotal</span><span>${{ number_format($invoice->subtotal, 2) }}</span></div>
                    @if ($invoice->discount > 0)
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Discount</span><span class="text-success">-${{ number_format($invoice->discount, 2) }}</span></div>
                    @endif
                    @if ($invoice->tax > 0)
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Tax</span><span>+${{ number_format($invoice->tax, 2) }}</span></div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span class="fw-bold">Total</span><span class="fw-bold">${{ number_format($invoice->total, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Amount Paid</span><span class="text-success">${{ number_format($invoice->amount_paid, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span class="fw-bold">Balance</span>
                        <span class="fw-bold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">${{ number_format($invoice->balance, 2) }}</span>
                    </div>
                </div>
            </div>

            @if ($invoice->payments->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payment History</h6></div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach ($invoice->payments as $payment)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold">${{ number_format($payment->amount, 2) }}</div>
                                            <small class="text-muted">{{ $payment->getPaymentMethodLabel() }}</small>
                                            @if ($payment->reference_number)<small class="text-muted">| {{ $payment->reference_number }}</small>@endif
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">{{ fmt_datetime($payment->paid_at) }}</small>
                                            @can('payment.view')<div><a href="{{ route('payments.receipt', $payment) }}" class="small">Receipt</a></div>@endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>
