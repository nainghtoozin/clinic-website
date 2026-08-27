<x-print-base
    documentTitle="Queue Ticket"
    :documentNumber="$ticket->ticket_number"
    :documentDate="fmt_date($ticket->queue_date, 'd M Y')"
    paperSize="receipt"
>
    <div class="text-center mb-3">
        <div style="font-size:32px; font-weight:700; color:var(--primary); line-height:1;">
            {{ $ticket->ticket_number }}
        </div>
        <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Your Queue Number</div>
    </div>

    <table class="table table-sm" style="font-size:12px;">
        <tr><td style="font-weight:600; width:80px;">Patient</td><td>{{ $ticket->patient->name ?? 'Walk-in' }}</td></tr>
        @if($ticket->patient?->patient_number)
            <tr><td style="font-weight:600;">ID</td><td>{{ $ticket->patient->patient_number }}</td></tr>
        @endif
        @if($ticket->doctor)
            <tr><td style="font-weight:600;">Doctor</td><td>Dr. {{ $ticket->doctor->name }}</td></tr>
        @endif
        @if($ticket->doctor?->department)
            <tr><td style="font-weight:600;">Dept</td><td>{{ $ticket->doctor->department->name }}</td></tr>
        @endif
        <tr><td style="font-weight:600;">Date</td><td>{{ fmt_date($ticket->queue_date, 'd M Y') }}</td></tr>
        <tr><td style="font-weight:600;">Time</td><td>{{ fmt_time($ticket->checked_in_at) }}</td></tr>
    </table>

    <div class="text-center" style="margin-top:10px; font-size:10px; color:var(--text-muted);">
        Please wait for your number to be called.
    </div>
</x-print-base>
