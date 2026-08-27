<x-print-base
    documentTitle="Patient Report"
    :documentDate="$startDate . ' to ' . $endDate"
    paperSize="a4"
>
    <div class="info-box mb-3">
        <h6>Report Period</h6>
        <p><strong>{{ fmt_date($startDate, 'd M Y') }} - {{ fmt_date($endDate, 'd M Y') }}</strong> | {{ $total }} new patient(s)</p>
    </div>

    @if($patients->isNotEmpty())
        <table class="doc-table">
            <thead>
                <tr><th>Date</th><th>Patient #</th><th>Name</th><th>Phone</th><th class="text-center">Visits</th><th class="text-center">Rx</th><th class="text-center">Invoices</th></tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                    <tr>
                        <td>{{ fmt_date($patient->created_at) }}</td>
                        <td>{{ $patient->patient_number }}</td>
                        <td>{{ $patient->name }}</td>
                        <td>{{ $patient->phone ?? '-' }}</td>
                        <td class="text-center">{{ $patient->appointments_count ?? 0 }}</td>
                        <td class="text-center">{{ $patient->prescriptions_count ?? 0 }}</td>
                        <td class="text-center">{{ $patient->invoices_count ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center text-muted">No patients found for this period.</p>
    @endif
</x-print-base>
