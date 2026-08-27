<x-print-base
    documentTitle="Laboratory Investigation Report"
    :documentNumber="$investigation->id ? 'INV-' . str_pad($investigation->id, 5, '0', STR_PAD_LEFT) : null"
    :documentDate="fmt_date($investigation->requested_date ?? $investigation->created_at)"
    paperSize="a4"
>
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="info-box">
                <h6>Patient Information</h6>
                <p><strong>{{ $investigation->patient->name ?? 'N/A' }}</strong></p>
                @if($investigation->patient?->patient_number)
                    <p><small class="text-muted">{{ $investigation->patient->patient_number }}</small></p>
                @endif
                @if($investigation->patient?->date_of_birth)<p>DOB: {{ fmt_date($investigation->patient->date_of_birth) }}</p>@endif
                @if($investigation->patient?->gender)<p>Gender: {{ ucfirst($investigation->patient->gender) }}</p>@endif
                @if($investigation->patient?->blood_group)<p>Blood Group: {{ $investigation->patient->blood_group }}</p>@endif
            </div>
        </div>
        <div class="col-6">
            <div class="info-box">
                <h6>Requesting Doctor</h6>
                <p><strong>{{ $investigation->doctor->name ?? 'N/A' }}</strong></p>
                @if($investigation->doctor?->title)<p>{{ $investigation->doctor->title }}</p>@endif
                @if($investigation->doctor?->department)<p>Department: {{ $investigation->doctor->department->name }}</p>@endif
            </div>
        </div>
    </div>

    <div class="info-box avoid-break mb-3">
        <h6>Test Details</h6>
        <table class="table table-sm mb-0">
            <tr><td style="width:160px; font-weight:600;">Test Name</td><td>{{ $investigation->labTest->name ?? 'N/A' }}</td></tr>
            @if($investigation->labTest?->code)
                <tr><td style="font-weight:600;">Test Code</td><td>{{ $investigation->labTest->code }}</td></tr>
            @endif
            @if($investigation->labTest?->sample_type)
                <tr><td style="font-weight:600;">Sample Type</td><td>{{ $investigation->labTest->sample_type }}</td></tr>
            @endif
            <tr>
                <td style="font-weight:600;">Priority</td>
                <td><span class="status-badge status-{{ $investigation->priority === 'stat' ? 'cancelled' : ($investigation->priority === 'urgent' ? 'pending' : 'paid') }}">{{ ucfirst($investigation->priority) }}</span></td>
            </tr>
            <tr>
                <td style="font-weight:600;">Status</td>
                <td><span class="status-badge status-{{ $investigation->status === 'completed' ? 'paid' : ($investigation->status === 'cancelled' ? 'cancelled' : 'pending') }}">{{ ucfirst(str_replace('_', ' ', $investigation->status)) }}</span></td>
            </tr>
            @if($investigation->clinical_notes)
                <tr><td style="font-weight:600;">Clinical Notes</td><td>{{ $investigation->clinical_notes }}</td></tr>
            @endif
        </table>
    </div>

    @if($investigation->status === 'completed')
        <div class="info-box avoid-break mb-3" style="border: 2px solid #059669;">
            <h6 style="color: #059669;">Result</h6>
            <table class="table table-sm mb-0">
                <tr><td style="width:160px; font-weight:600;">Result Value</td><td><strong>{{ $investigation->result_value ?? 'N/A' }}</strong></td></tr>
                @if($investigation->result_unit)
                    <tr><td style="font-weight:600;">Unit</td><td>{{ $investigation->result_unit }}</td></tr>
                @endif
                @if($investigation->result_reference_range)
                    <tr><td style="font-weight:600;">Reference Range</td><td>{{ $investigation->result_reference_range }}</td></tr>
                @endif
                @if($investigation->labTest?->reference_range && !$investigation->result_reference_range)
                    <tr><td style="font-weight:600;">Reference Range</td><td>{{ $investigation->labTest->reference_range }}</td></tr>
                @endif
                @if($investigation->interpretation)
                    <tr><td style="font-weight:600;">Interpretation</td><td>{{ $investigation->interpretation }}</td></tr>
                @endif
                @if($investigation->resulted_at)
                    <tr><td style="font-weight:600;">Reported On</td><td>{{ fmt_datetime($investigation->resulted_at) }}</td></tr>
                @endif
            </table>
        </div>
    @endif

    <div class="signature-area">
        <div class="signature-line">Lab Technician</div>
        <div class="signature-line">Authorised Signatory</div>
    </div>
</x-print-base>
