<x-print-base
    documentTitle="Appointment Report"
    :documentDate="__('app.report.period') ?? $startDate . ' to ' . $endDate"
    paperSize="a4"
>
    <div class="info-box mb-3">
        <h6>Report Period</h6>
        <p><strong>{{ fmt_date($startDate, 'd M Y') }} - {{ fmt_date($endDate, 'd M Y') }}</strong></p>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-3">
            <div class="info-box text-center">
                <h6>Total</h6>
                <p style="font-size:18px; font-weight:700;">{{ $total }}</p>
            </div>
        </div>
        <div class="col-3">
            <div class="info-box text-center">
                <h6>Completed</h6>
                <p style="font-size:18px; font-weight:700; color:#059669;">{{ $completed }}</p>
            </div>
        </div>
        <div class="col-3">
            <div class="info-box text-center">
                <h6>Cancelled</h6>
                <p style="font-size:18px; font-weight:700; color:#dc2626;">{{ $cancelled }}</p>
            </div>
        </div>
        <div class="col-3">
            <div class="info-box text-center">
                <h6>Pending</h6>
                <p style="font-size:18px; font-weight:700; color:#d97706;">{{ $pending }}</p>
            </div>
        </div>
    </div>

    @if($appointments->isNotEmpty())
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Number</th><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($appointments as $apt)
                    <tr>
                        <td>{{ fmt_date($apt->date) }}</td>
                        <td>{{ $apt->appointment_number }}</td>
                        <td>{{ $apt->patient->name ?? 'N/A' }}</td>
                        <td>Dr. {{ $apt->doctor->name ?? 'N/A' }}</td>
                        <td>{{ $apt->time }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $apt->status)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center text-muted">No appointments found for this period.</p>
    @endif
</x-print-base>
