<x-print-base
    documentTitle="Payment Receipt"
    :documentNumber="'RCT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT)"
    :documentDate="fmt_datetime($payment->paid_at)"
    paperSize="receipt"
>
    <div class="info-box mb-3">
        <h6>Payment Details</h6>
        <table class="table table-sm mb-0" style="font-size:12px;">
            <tr><td style="width:100px; font-weight:600;">Amount Paid</td><td style="font-size:16px; font-weight:700; color:#059669;">{{ fmt_money($payment->amount) }}</td></tr>
            <tr><td style="font-weight:600;">Invoice #</td><td>{{ $payment->invoice->invoice_number ?? 'N/A' }}</td></tr>
            <tr><td style="font-weight:600;">Date</td><td>{{ fmt_datetime($payment->paid_at) }}</td></tr>
            <tr><td style="font-weight:600;">Method</td><td>{{ $payment->getPaymentMethodLabel() }}</td></tr>
            @if($payment->reference_number)
                <tr><td style="font-weight:600;">Reference</td><td>{{ $payment->reference_number }}</td></tr>
            @endif
        </table>
    </div>

    <div class="info-box mb-3">
        <h6>Patient</h6>
        <p style="font-size:12px; margin:0;"><strong>{{ $payment->invoice->patient->name ?? 'N/A' }}</strong></p>
        @if($payment->invoice->patient?->patient_number)
            <p style="font-size:11px; margin:0; color:var(--text-muted);">{{ $payment->invoice->patient->patient_number }}</p>
        @endif
    </div>

    <div class="info-box mb-3">
        <h6>Invoice Summary</h6>
        <table class="table table-sm mb-0" style="font-size:12px;">
            <tr><td>Subtotal</td><td class="text-end">{{ fmt_money($payment->invoice->subtotal) }}</td></tr>
            @if($payment->invoice->discount > 0)
                <tr><td>Discount</td><td class="text-end">-{{ fmt_money($payment->invoice->discount) }}</td></tr>
            @endif
            @if($payment->invoice->tax > 0)
                <tr><td>Tax</td><td class="text-end">{{ fmt_money($payment->invoice->tax) }}</td></tr>
            @endif
            <tr style="font-weight:600;"><td>Total</td><td class="text-end">{{ fmt_money($payment->invoice->total) }}</td></tr>
            <tr><td>Paid to Date</td><td class="text-end" style="color:#059669;">{{ fmt_money($payment->invoice->amount_paid) }}</td></tr>
            @if($payment->invoice->balance > 0)
                <tr><td>Balance Due</td><td class="text-end" style="color:#dc2626;">{{ fmt_money($payment->invoice->balance) }}</td></tr>
            @endif
        </table>
    </div>

    @if($payment->notes)
        <p style="font-size:11px; color:var(--text-muted);"><strong>Note:</strong> {{ $payment->notes }}</p>
    @endif

    @if($payment->recordedBy)
        <p style="font-size:11px; color:var(--text-muted); margin-top:10px;">Received by: {{ $payment->recordedBy->name }}</p>
    @endif
</x-print-base>
