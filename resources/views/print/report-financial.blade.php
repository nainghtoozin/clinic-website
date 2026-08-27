<x-print-base
    documentTitle="Financial Report"
    :documentDate="$startDate . ' to ' . $endDate"
    paperSize="a4"
>
    <div class="info-box mb-3">
        <h6>Report Period</h6>
        <p><strong>{{ fmt_date($startDate, 'd M Y') }} - {{ fmt_date($endDate, 'd M Y') }}</strong></p>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-3">
            <div class="info-box text-center">
                <h6>Revenue</h6>
                <p style="font-size:18px; font-weight:700; color:#059669;">{{ fmt_money($totalRevenue) }}</p>
            </div>
        </div>
        <div class="col-3">
            <div class="info-box text-center">
                <h6>Collected</h6>
                <p style="font-size:18px; font-weight:700; color:#2563eb;">{{ fmt_money($totalPaid) }}</p>
            </div>
        </div>
        <div class="col-3">
            <div class="info-box text-center">
                <h6>Expenses</h6>
                <p style="font-size:18px; font-weight:700; color:#dc2626;">{{ fmt_money($totalExpenses) }}</p>
            </div>
        </div>
        <div class="col-3">
            <div class="info-box text-center">
                <h6>Net Income</h6>
                <p style="font-size:18px; font-weight:700; color:{{ $netIncome >= 0 ? '#059669' : '#dc2626' }};">{{ fmt_money($netIncome) }}</p>
            </div>
        </div>
    </div>

    @if($invoices->isNotEmpty())
        <h6 class="mt-4 mb-2" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Invoices ({{ $invoices->count() }})</h6>
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Invoice #</th><th>Patient</th><th class="text-end">Total</th><th class="text-end">Paid</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ fmt_date($invoice->created_at) }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->patient->name ?? 'N/A' }}</td>
                        <td class="text-end">{{ fmt_money($invoice->total) }}</td>
                        <td class="text-end">{{ fmt_money($invoice->amount_paid) }}</td>
                        <td>{{ $invoice->getStatusLabel() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($expenses->isNotEmpty())
        <h6 class="mt-4 mb-2" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Expenses ({{ $expenses->count() }})</h6>
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Description</th><th>Category</th><th class="text-end">Amount</th></tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                    <tr>
                        <td>{{ fmt_date($expense->expense_date) }}</td>
                        <td>{{ $expense->description }}</td>
                        <td>{{ $expense->category ?? '-' }}</td>
                        <td class="text-end">{{ fmt_money($expense->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-base>
