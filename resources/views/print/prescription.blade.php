<x-print-base
    documentTitle="Prescription"
    :documentNumber="$prescription->prescription_number"
    :documentDate="fmt_date($prescription->prescribed_date ?? $prescription->created_at)"
    paperSize="a5"
>
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="info-box">
                <h6>Patient</h6>
                <p><strong>{{ $prescription->patient->name ?? 'N/A' }}</strong></p>
                @if($prescription->patient?->patient_number)
                    <p><small class="text-muted">{{ $prescription->patient->patient_number }}</small></p>
                @endif
                @if($prescription->patient?->date_of_birth)
                    <p>DOB: {{ fmt_date($prescription->patient->date_of_birth) }}</p>
                @endif
                @if($prescription->patient?->blood_group)
                    <p>Blood Group: {{ $prescription->patient->blood_group }}</p>
                @endif
                @if($prescription->patient?->allergies)
                    <p class="text-danger"><strong>Allergies:</strong> {{ $prescription->patient->allergies }}</p>
                @endif
            </div>
        </div>
        <div class="col-6">
            <div class="info-box">
                <h6>Prescribing Doctor</h6>
                <p><strong>{{ $prescription->doctor->name ?? 'N/A' }}</strong></p>
                @if($prescription->doctor?->title)<p>{{ $prescription->doctor->title }}</p>@endif
                @if($prescription->doctor?->qualifications)<p><small class="text-muted">{{ $prescription->doctor->qualifications }}</small></p>@endif
                @if($prescription->doctor?->department)<p>Dept: {{ $prescription->doctor->department->name }}</p>@endif
            </div>
        </div>
    </div>

    <div class="avoid-break">
        <h6 style="font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); margin-bottom:8px;">Medications</h6>
        <table class="doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medicine</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prescription->items as $index => $item)
                    <tr class="avoid-break">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->medicine->name ?? 'N/A' }}</strong>
                            @if($item->medicine?->strength)<br><small class="text-muted">{{ $item->medicine->strength }}</small>@endif
                            @if($item->medicine?->form)<br><small class="text-muted">{{ $item->medicine->form }}</small>@endif
                        </td>
                        <td>{{ $item->dosage ?? '-' }}</td>
                        <td>{{ $item->frequency ?? '-' }}</td>
                        <td>{{ $item->duration ?? '-' }}</td>
                        <td>{{ $item->quantity ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No medications prescribed</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($prescription->items->contains(function ($item) { return !empty($item->instructions); }))
        <div class="avoid-break" style="margin-top:10px;">
            <h6 style="font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); margin-bottom:6px;">Special Instructions</h6>
            @foreach($prescription->items as $item)
                @if($item->instructions)
                    <p style="font-size:12px; margin-bottom:3px;">
                        <strong>{{ $item->medicine->name ?? 'Medicine' }}:</strong> {{ $item->instructions }}
                    </p>
                @endif
            @endforeach
        </div>
    @endif

    @if($prescription->notes)
        <div class="avoid-break" style="margin-top:10px;">
            <h6 style="font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); margin-bottom:6px;">Notes</h6>
            <p style="font-size:12px;">{{ $prescription->notes }}</p>
        </div>
    @endif

    <div class="signature-area">
        <div class="signature-line">Patient / Guardian</div>
        <div class="signature-line">Doctor's Signature & Seal</div>
    </div>
</x-print-base>
