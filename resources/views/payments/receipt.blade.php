<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
        }
        body { background: #f5f7fb; }
        .receipt { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body class="p-4">
    <div class="receipt">
        <div class="no-print mb-3 text-end">
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i> Print Receipt</button>
            <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn btn-outline-secondary ms-2">Back to Invoice</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h4 class="mb-0">Clinic Name</h4>
                    <small class="text-muted">Clinic Address & Contact Info</small>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <small class="text-muted">Receipt For</small>
                        <div class="fw-semibold">{{ $payment->invoice->patient->name ?? '-' }}</div>
                        <small class="text-muted">{{ $payment->invoice->patient->patient_number ?? '' }}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Invoice #</small>
                        <div><span class="badge bg-primary">{{ $payment->invoice->invoice_number }}</span></div>
                        <small class="text-muted">Payment Date</small>
                        <div>{{ $payment->paid_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>

                <hr>

                <table class="table table-sm mb-3">
                    <thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @foreach ($payment->invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }} <small class="text-muted">x{{ $item->quantity }}</small></td>
                                <td class="text-end">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr>

                <div class="row mb-3">
                    <div class="col-6">
                        <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>${{ number_format($payment->invoice->subtotal, 2) }}</span></div>
                        @if ($payment->invoice->discount > 0)
                            <div class="d-flex justify-content-between"><span class="text-muted">Discount</span><span>-${{ number_format($payment->invoice->discount, 2) }}</span></div>
                        @endif
                        @if ($payment->invoice->tax > 0)
                            <div class="d-flex justify-content-between"><span class="text-muted">Tax</span><span>+${{ number_format($payment->invoice->tax, 2) }}</span></div>
                        @endif
                        <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>${{ number_format($payment->invoice->total, 2) }}</span></div>
                    </div>
                    <div class="col-6 text-end">
                        <div class="d-flex justify-content-between"><span class="text-muted">Amount Paid</span><span class="text-success">${{ number_format($payment->invoice->amount_paid, 2) }}</span></div>
                        <div class="d-flex justify-content-between fw-bold"><span>Balance</span>
                            <span class="{{ $payment->invoice->balance > 0 ? 'text-danger' : 'text-success' }}">${{ number_format($payment->invoice->balance, 2) }}</span>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Payment Method</small>
                        <div class="fw-semibold">{{ $payment->getPaymentMethodLabel() }}</div>
                    </div>
                    @if ($payment->reference_number)
                        <div class="col-6">
                            <small class="text-muted">Reference #</small>
                            <div class="fw-semibold">{{ $payment->reference_number }}</div>
                        </div>
                    @endif
                </div>

                @if ($payment->recordedBy)
                    <div class="text-center mt-4">
                        <small class="text-muted">Recorded by {{ $payment->recordedBy->name }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
