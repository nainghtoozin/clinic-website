<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Prescriptions</h5>
            <small class="text-muted">{{ $prescriptions->total() }} prescriptions</small>
        </div>
        @can('consultation.view')
            <a href="{{ route('consultations.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clipboard2-pulse me-1"></i> Open a Consultation
            </a>
        @endcan
    </div>
    <div class="alert alert-info py-2 small">
        <i class="bi bi-info-circle me-1"></i>
        Prescriptions are created from a patient's consultation. Open a consultation and choose
        <strong>Add Prescription</strong>.
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by prescription # or patient name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="patient_id" class="form-select form-select-sm">
                        <option value="">All Patients</option>
                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" {{ request('patient_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->patient_number }} - {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="doctor_id" class="form-select form-select-sm">
                        <option value="">All Doctors</option>
                        @foreach ($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                {{ $doc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                </div>
                @if (request()->hasAny(['search', 'patient_id', 'doctor_id']))
                    <div class="col-auto">
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($prescriptions->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-file-medical fs-1 d-block mb-2"></i>
                    <div>No prescriptions found.</div>
                    @can('prescription.create')
                        <a href="{{ route('prescriptions.create') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-plus-circle me-1"></i> Create Prescription
                        </a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Prescription #</th>
                                <th>Patient</th>
                                <th class="d-none d-md-table-cell">Doctor</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prescriptions as $prescription)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $prescription->prescription_number }}</span>
                                    </td>
                                    <td>{{ $prescription->patient->name ?? '-' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $prescription->doctor->name ?? '-' }}</td>
                                    <td>{{ fmt_date($prescription->prescribed_date) }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $prescription->items->count() }} items</span>
                                    </td>
                                    <td class="text-end">${{ number_format($prescription->total, 2) }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('prescription.edit')
                                                <a href="{{ route('prescriptions.edit', $prescription) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $prescriptions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>
