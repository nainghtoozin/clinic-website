<x-auth-layout>
    <x-page-header title="Consultations" subtitle="Manage clinical consultations and records"
        :breadcrumbs="[['label' => 'Consultations']]">
        @can('consultation.create')
            <a href="{{ route('consultations.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> New Consultation
            </a>
        @endcan
    </x-page-header>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('consultations.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search patient, patient #, diagnosis, symptoms..." value="{{ request('search') }}">
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
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'doctor_id', 'status']))
                            <a href="{{ route('consultations.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
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
            <i class="bi bi-clipboard2-pulse me-1"></i>{{ $consultations->total() }} consultation(s)
            @if (request()->has('search') && request('search'))
                @php($searchTerm = request('search'))
                &middot; matching &ldquo;{{ $searchTerm }}&rdquo;
            @endif
        </small>
        @if (request()->hasAny(['search', 'doctor_id', 'status']))
            <a href="{{ route('consultations.index') }}" class="small text-decoration-none">
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
                        <th>Date</th>
                        <th>Patient</th>
                        <th class="d-none d-md-table-cell">Doctor</th>
                        <th class="d-none d-lg-table-cell">Diagnosis</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $consultation)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ fmt_date($consultation->created_at) }}</span>
                                <small class="text-muted d-block">{{ fmt_time($consultation->created_at) }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-primary">{{ initials($consultation->patient?->name) }}</span>
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $consultation->patient?->name ?? '-' }}</div>
                                        @if ($consultation->patient)
                                            <small class="text-muted">{{ $consultation->patient->patient_number }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $consultation->doctor?->name ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell">{{ Str::limit($consultation->diagnosis ?? '-', 30) }}</td>
                            <td>
                                <span class="badge {{ $consultation->isCompleted() ? 'bg-success' : 'bg-warning text-dark' }}">
                                    <span class="status-dot"></span>{{ ucfirst($consultation->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if ($consultation->isDraft())
                                        @can('consultation.edit')
                                            <a href="{{ route('consultations.edit', $consultation) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('consultation.complete')
                                            <form method="POST" action="{{ route('consultations.complete', $consultation) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" title="Complete"
                                                    onclick="return confirm('Complete this consultation?')">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-clipboard-check fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No consultations found</div>
                                <small>Try adjusting your search or filters.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($consultations->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $consultations->firstItem() }}&ndash;{{ $consultations->lastItem() }} of {{ $consultations->total() }}
            </small>
            <div>{{ $consultations->links() }}</div>
        </div>
    @endif
</x-auth-layout>
