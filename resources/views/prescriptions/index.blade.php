<x-auth-layout>
    <x-page-header title="Prescriptions" subtitle="Prescriptions issued from patient consultations"
        :breadcrumbs="[['label' => 'Prescriptions']]">
        @can('consultation.view')
            <a href="{{ route('consultations.index') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-clipboard2-pulse me-1"></i> Open a Consultation
            </a>
        @endcan
    </x-page-header>

    <div class="alert alert-info py-2 small">
        <i class="bi bi-info-circle me-1"></i>
        Prescriptions are created from a patient's consultation. Open a consultation and choose
        <strong>Add Prescription</strong>.
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('prescriptions.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search by prescription # or patient name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Patient</label>
                        <select name="patient_id" class="form-select form-select-sm">
                            <option value="">All Patients</option>
                            @foreach ($patients as $p)
                                <option value="{{ $p->id }}" {{ request('patient_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->patient_number }} - {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All Doctors</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'patient_id', 'doctor_id']))
                            <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results summary --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-file-medical me-1"></i>{{ $prescriptions->total() }} prescription(s)
            @if (request()->has('search') && request('search'))
                @php($searchTerm = request('search'))
                &middot; matching &ldquo;{{ $searchTerm }}&rdquo;
            @endif
        </small>
        @if (request()->hasAny(['search', 'patient_id', 'doctor_id']))
            <a href="{{ route('prescriptions.index') }}" class="small text-decoration-none">
                <i class="bi bi-slash-circle me-1"></i>Clear filters
            </a>
        @endif
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
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
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prescriptions as $prescription)
                        <tr>
                            <td>
                                <span class="badge bg-primary">{{ $prescription->prescription_number }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-primary">{{ initials($prescription->patient?->name) }}</span>
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $prescription->patient?->name ?? '-' }}</div>
                                        @if ($prescription->patient)
                                            <small class="text-muted">{{ $prescription->patient->patient_number }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $prescription->doctor?->name ?? '-' }}</td>
                            <td>{{ fmt_date($prescription->prescribed_date) }}</td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $prescription->items->count() }} items</span>
                            </td>
                            <td class="text-end fw-semibold">${{ number_format($prescription->total, 2) }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('prescription.edit')
                                        <a href="{{ route('prescriptions.edit', $prescription) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('prescription.delete')
                                        <form method="POST" action="{{ route('prescriptions.destroy', $prescription) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="return confirm('Delete this prescription?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-file-medical fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No prescriptions found</div>
                                <small>Try adjusting your search or filters.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($prescriptions->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $prescriptions->firstItem() }}&ndash;{{ $prescriptions->lastItem() }} of {{ $prescriptions->total() }}
            </small>
            <div>{{ $prescriptions->links() }}</div>
        </div>
    @endif
</x-auth-layout>
