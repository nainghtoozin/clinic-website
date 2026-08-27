<x-print-base
    documentTitle="Appointment Confirmation"
    :documentNumber="$appointment->appointment_number"
    :documentDate="fmt_date($appointment->date)"
    paperSize="a4"
>
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="info-box">
                <h6>Patient Information</h6>
                <p><strong>{{ $appointment->patient->name ?? 'N/A' }}</strong></p>
                @if($appointment->patient?->patient_number)
                    <p><small class="text-muted">{{ $appointment->patient->patient_number }}</small></p>
                @endif
                @if($appointment->patient?->phone)<p>Phone: {{ $appointment->patient->phone }}</p>@endif
                @if($appointment->patient?->email)<p>Email: {{ $appointment->patient->email }}</p>@endif
            </div>
        </div>
        <div class="col-6">
            <div class="info-box">
                <h6>Doctor Information</h6>
                <p><strong>{{ $appointment->doctor->name ?? 'N/A' }}</strong></p>
                @if($appointment->doctor?->title)<p>{{ $appointment->doctor->title }}</p>@endif
                @if($appointment->department)<p>Department: {{ $appointment->department->name }}</p>@endif
            </div>
        </div>
    </div>

    <div class="info-box avoid-break">
        <h6>Appointment Details</h6>
        <table class="table table-sm mb-0">
            <tr><td style="width:150px; font-weight:600;">Date</td><td>{{ fmt_date($appointment->date, 'l, d M Y') }}</td></tr>
            <tr><td style="font-weight:600;">Time</td><td>{{ $appointment->time }}</td></tr>
            <tr><td style="font-weight:600;">Duration</td><td>{{ $appointment->duration ?? 30 }} minutes</td></tr>
            <tr><td style="font-weight:600;">Status</td><td><span class="status-badge status-{{ $appointment->status === 'completed' ? 'paid' : ($appointment->status === 'cancelled' ? 'cancelled' : 'pending') }}">{{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</span></td></tr>
            @if($appointment->message)
                <tr><td style="font-weight:600;">Notes</td><td>{{ $appointment->message }}</td></tr>
            @endif
        </table>
    </div>

    @if($appointment->patient?->allergies)
        <div class="alert alert-warning avoid-break" style="font-size:12px;">
            <strong>Allergies:</strong> {{ $appointment->patient->allergies }}
        </div>
    @endif

    <div class="signature-area">
        <div class="signature-line">Patient Signature</div>
        <div class="signature-line">Doctor / Authorised Signatory</div>
    </div>
</x-print-base>
