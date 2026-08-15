<x-auth-layout>
    <x-page-header title="Prescription {{ $prescription->prescription_number }}" subtitle="{{ $prescription->patient?->name ?? '' }} &middot; {{ fmt_date($prescription->prescribed_date) }}"
        :breadcrumbs="[['label' => 'Prescriptions', 'url' => route('prescriptions.index')], ['label' => $prescription->prescription_number]]">
        @can('prescription.edit')
            <a href="{{ route('prescriptions.edit', $prescription) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-file-medical me-2"></i>Prescription Information</h6>
                    <span class="badge bg-primary">{{ $prescription->prescription_number }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Patient</label>
                            <div class="fw-semibold">
                                <a href="{{ route('patients.show', $prescription->patient) }}">{{ $prescription->patient->name ?? '-' }}</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Doctor</label>
                            <div>{{ $prescription->doctor->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Prescribed Date</label>
                            <div>{{ fmt_date($prescription->prescribed_date) }}</div>
                        </div>
                        @if ($prescription->consultation)
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Consultation</label>
                                <div><a href="{{ route('consultations.show', $prescription->consultation) }}">View Consultation</a></div>
                            </div>
                        @endif
                        @if ($prescription->notes)
                            <div class="col-12">
                                <label class="form-label text-muted small">Notes</label>
                                <div class="border rounded p-3 bg-light">{{ $prescription->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-capsule me-2"></i>Medications</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Medicine</th>
                                    <th>Dosage</th>
                                    <th>Frequency</th>
                                    <th>Duration</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prescription->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->medicine->name ?? '-' }}</div>
                                            <small class="text-muted">{{ $item->medicine->strength ?? '' }}</small>
                                        </td>
                                        <td>{{ $item->dosage }}</td>
                                        <td>{{ $item->frequency }}</td>
                                        <td>{{ $item->duration ?? '-' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                    @if ($item->instructions)
                                        <tr>
                                            <td colspan="6" class="text-muted small">
                                                <i class="bi bi-info-circle me-1"></i> {{ $item->instructions }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Total</td>
                                    <td class="fw-bold">${{ number_format($prescription->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Items</span>
                        <span class="fw-semibold">{{ $prescription->items->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Cost</span>
                        <span class="fw-bold">${{ number_format($prescription->total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Created</span>
                        <span>{{ fmt_datetime($prescription->created_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>