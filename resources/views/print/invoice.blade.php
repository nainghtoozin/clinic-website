<x-print-base
    documentTitle="Invoice"
    :documentNumber="$invoice->invoice_number"
    :documentDate="fmt_date($invoice->issued_at ?? $invoice->created_at)"
    paperSize="a4"
>
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="info-box">
                <h6>Bill To</h6>
                <p><strong>{{ $invoice->patient->name ?? 'N/A' }}</strong></p>
                @if($invoice->patient?->patient_number)<p><small class="text-muted">{{ $invoice->patient->patient_number }}</small></p>@endif
                @if($invoice->patient?->phone)<p>Phone: {{ $invoice->patient->phone }}</p>@endif
                @if($invoice->patient?->email)<p>Email: {{ $invoice->patient->email }}</p>@endif
                @if($invoice->patient?->address)<p>{{ $invoice->patient->address }}</p>@endif
            </div>
        </div>
        <div class="col-6">
            <div class="info-box">
                <h6>Invoice Details</h6>
                <table class="table table-sm mb-0">
                    <tr><td style="width:100px;">Invoice #</td><td><strong>{{ $invoice->invoice_number }}</strong></td></tr>
                    <tr><td>Date</td><td>{{ fmt_date($invoice->issued_at ?? $invoice->created_at) }}</td></tr>
                    @if($invoice->doctor)
                        <tr><td>Doctor</td><td>Dr. {{ $invoice->doctor->name }}</td></tr>
                    @endif
                    <tr>
                        <td>Status</td>
                        <td><span class="status-badge status-{{ $invoice->status === 'paid' ? 'paid' : ($invoice->status === 'cancelled' ? 'cancelled' : ($invoice->status === 'partially_paid' ? 'partial' : 'pending')) }}">{{ $invoice->getStatusLabel() }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="avoid-break">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->getTypeLabel() }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ fmt_money($item->unit_price) }}</td>
                        <td class="text-end">{{ fmt_money($item->total) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No items</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="doc-totals">
        <table>
            <tr><td>Subtotal</td><td class="text-end">{{ fmt_money($invoice->subtotal) }}</td></tr>
            @if($invoice->discount > 0)
                <tr><td>Discount</td><td class="text-end">-{{ fmt_money($invoice->discount) }}</td></tr>
            @endif
            @if($invoice->tax > 0)
                <tr><td>Tax</td><td class="text-end">{{ fmt_money($invoice->tax) }}</td></tr>
            @endif
            <tr class="total-row"><td>Total</td><td class="text-end">{{ fmt_money($invoice->total) }}</td></tr>
            @if($invoice->amount_paid > 0)
                <tr><td>Paid</td><td class="text-end" style="color:#059669;">-{{ fmt_money($invoice->amount_paid) }}</td></tr>
            @endif
            @if($invoice->balance > 0)
                <tr><td><strong>Balance Due</strong></td><td class="text-end"><strong style="color:#dc2626;">{{ fmt_money($invoice->balance) }}</strong></td></tr>
            @endif
        </table>
    </div>

    @if($invoice->payments->isNotEmpty())
        <div class="avoid-break" style="margin-top:15px;">
            <h6 style="font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); margin-bottom:8px;">Payment History</h6>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ fmt_datetime($payment->paid_at) }}</td>
                            <td>{{ $payment->getPaymentMethodLabel() }}</td>
                            <td>{{ $payment->reference_number ?? '-' }}</td>
                            <td class="text-end">{{ fmt_money($payment->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($invoice->notes)
        <div class="avoid-break" style="margin-top:10px;">
            <h6 style="font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); margin-bottom:6px;">Notes</h6>
            <p style="font-size:12px;">{{ $invoice->notes }}</p>
        </div>
    @endif
</x-print-base>
