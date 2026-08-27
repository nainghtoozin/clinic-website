<x-print-base
    documentTitle="Inventory Report"
    :documentDate="$startDate . ' to ' . $endDate"
    paperSize="a4"
>
    <div class="info-box mb-3">
        <h6>Report Period</h6>
        <p><strong>{{ fmt_date($startDate, 'd M Y') }} - {{ fmt_date($endDate, 'd M Y') }}</strong> | {{ $total }} movement(s)</p>
    </div>

    @if($movements->isNotEmpty())
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Medicine</th><th>Type</th><th class="text-end">Qty</th><th>Reason</th><th>User</th></tr>
            </thead>
            <tbody>
                @foreach($movements as $movement)
                    <tr>
                        <td>{{ fmt_datetime($movement->created_at) }}</td>
                        <td>{{ $movement->medicine->name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($movement->type ?? '-') }}</td>
                        <td class="text-end">{{ $movement->quantity ?? '-' }}</td>
                        <td>{{ $movement->reason ?? '-' }}</td>
                        <td>{{ $movement->performer->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center text-muted">No inventory movements found for this period.</p>
    @endif
</x-print-base>
