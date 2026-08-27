<x-print-base
    documentTitle="Patient Medical Record"
    :documentNumber="$patient->patient_number"
    :documentDate="'Printed on ' . now()->format('d M Y')"
    paperSize="a4"
>
    <div class="info-box avoid-break mb-3">
        <h6>Patient Profile</h6>
        <div class="row">
            <div class="col-6">
                <p><strong>{{ $patient->name }}</strong></p>
                <p>Patient #: {{ $patient->patient_number }}</p>
                @if($patient->date_of_birth)<p>DOB: {{ fmt_date($patient->date_of_birth) }}</p>@endif
                @if($patient->gender)<p>Gender: {{ ucfirst($patient->gender) }}</p>@endif
                @if($patient->blood_group)<p>Blood Group: {{ $patient->blood_group }}</p>@endif
            </div>
            <div class="col-6">
                @if($patient->phone)<p>Phone: {{ $patient->phone }}</p>@endif
                @if($patient->email)<p>Email: {{ $patient->email }}</p>@endif
                @if($patient->address)<p>Address: {{ $patient->address }}</p>@endif
            </div>
        </div>
    </div>

    @if($patient->allergies || $patient->medical_history)
        <div class="info-box avoid-break mb-3" style="border-left: 4px solid #dc2626;">
            <h6 style="color:#dc2626;">Medical Alerts</h6>
            @if($patient->allergies)<p><strong>Allergies:</strong> {{ $patient->allergies }}</p>@endif
            @if($patient->medical_history)<p><strong>Medical History:</strong> {{ $patient->medical_history }}</p>@endif
        </div>
    @endif

    @if(isset($consultations) && $consultations->isNotEmpty())
        <h6 class="mt-4 mb-2" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Consultations</h6>
        @foreach($consultations as $consultation)
            <div class="info-box avoid-break mb-2">
                <div class="d-flex justify-content-between">
                    <strong>{{ fmt_date($consultation->created_at) }}</strong>
                    <span class="text-muted">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</span>
                </div>
                @if($consultation->symptoms)<p style="margin-bottom:3px;"><strong>Symptoms:</strong> {{ $consultation->symptoms }}</p>@endif
                @if($consultation->diagnosis)<p style="margin-bottom:3px;"><strong>Diagnosis:</strong> {{ $consultation->diagnosis }}</p>@endif
                @if($consultation->treatment_plan)<p style="margin-bottom:3px;"><strong>Treatment:</strong> {{ $consultation->treatment_plan }}</p>@endif
            </div>
        @endforeach
    @endif

    @if(isset($vitalSigns) && $vitalSigns->isNotEmpty())
        <h6 class="mt-4 mb-2" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Vital Signs History</h6>
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>BP</th><th>Temp</th><th>Pulse</th><th>SpO2</th><th>Weight</th><th>Height</th></tr>
            </thead>
            <tbody>
                @foreach($vitalSigns as $vital)
                    <tr>
                        <td>{{ fmt_date($vital->recorded_at ?? $vital->created_at) }}</td>
                        <td>{{ $vital->blood_pressure ?? '-' }}</td>
                        <td>{{ $vital->temperature ? $vital->temperature . ' C' : '-' }}</td>
                        <td>{{ $vital->pulse ? $vital->pulse . ' bpm' : '-' }}</td>
                        <td>{{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : '-' }}</td>
                        <td>{{ $vital->weight ? $vital->weight . ' kg' : '-' }}</td>
                        <td>{{ $vital->height ? $vital->height . ' cm' : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($prescriptions) && $prescriptions->isNotEmpty())
        <h6 class="mt-4 mb-2" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Prescriptions</h6>
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Number</th><th>Doctor</th><th>Medications</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($prescriptions as $prescription)
                    <tr>
                        <td>{{ fmt_date($prescription->prescribed_date ?? $prescription->created_at) }}</td>
                        <td>{{ $prescription->prescription_number }}</td>
                        <td>Dr. {{ $prescription->doctor->name ?? 'N/A' }}</td>
                        <td>{{ $prescription->items->count() }} medication(s)</td>
                        <td>{{ ucfirst($prescription->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($investigations) && $investigations->isNotEmpty())
        <h6 class="mt-4 mb-2" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Investigations</h6>
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Test</th><th>Doctor</th><th>Result</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($investigations as $investigation)
                    <tr>
                        <td>{{ fmt_date($investigation->requested_date ?? $investigation->created_at) }}</td>
                        <td>{{ $investigation->labTest->name ?? 'N/A' }}</td>
                        <td>Dr. {{ $investigation->doctor->name ?? 'N/A' }}</td>
                        <td>{{ $investigation->result_value ?? '-' }} {{ $investigation->result_unit ?? '' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $investigation->status)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($appointments) && $appointments->isNotEmpty())
        <h6 class="mt-4 mb-2" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Appointment History</h6>
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Number</th><th>Doctor</th><th>Time</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($appointments as $appointment)
                    <tr>
                        <td>{{ fmt_date($appointment->date) }}</td>
                        <td>{{ $appointment->appointment_number }}</td>
                        <td>Dr. {{ $appointment->doctor->name ?? 'N/A' }}</td>
                        <td>{{ $appointment->time }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($invoices) && $invoices->isNotEmpty())
        <h6 class="mt-4 mb-2" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Billing History</h6>
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Invoice #</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Balance</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ fmt_date($invoice->issued_at ?? $invoice->created_at) }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td class="text-end">{{ fmt_money($invoice->total) }}</td>
                        <td class="text-end">{{ fmt_money($invoice->amount_paid) }}</td>
                        <td class="text-end">{{ fmt_money($invoice->balance) }}</td>
                        <td>{{ $invoice->getStatusLabel() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-base>
