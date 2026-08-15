<x-auth-layout>
    <x-page-header title="Patients" subtitle="Manage patient records and medical histories"
        :breadcrumbs="[['label' => 'Patients']]">
        @can('patient.create')
            <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> Add Patient
            </a>
        @endcan
    </x-page-header>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('patients.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, number, phone, or email..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Gender</label>
                        <select name="gender" class="form-select form-select-sm">
                            <option value="">All Gender</option>
                            <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ request('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'status', 'gender']))
                            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
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
            <i class="bi bi-people me-1"></i>{{ $patients->total() }} patient(s)
            @if (request()->has('search') && request('search'))
                @php($searchTerm = request('search'))
                &middot; matching &ldquo;{{ $searchTerm }}&rdquo;
            @endif
        </small>
        @if (request()->hasAny(['search', 'status', 'gender']))
            <a href="{{ route('patients.index') }}" class="small text-decoration-none">
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
                        <th>Patient ID</th>
                        <th>Patient</th>
                        <th class="d-none d-md-table-cell">Gender</th>
                        <th class="d-none d-md-table-cell">Age / DOB</th>
                        <th class="d-none d-lg-table-cell">Phone</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td>
                                <span class="badge bg-primary">{{ $patient->patient_number }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-primary">{{ initials($patient->name) }}</span>
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $patient->name }}</div>
                                        @if ($patient->email)
                                            <small class="text-muted text-truncate d-block" style="max-width:200px;">{{ $patient->email }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ ucfirst($patient->gender ?? '-') }}</td>
                            <td class="d-none d-md-table-cell">
                                @if ($patient->date_of_birth)
                                    <span class="fw-semibold">{{ \Illuminate\Support\Carbon::parse($patient->date_of_birth)->age }} yrs</span>
                                    <small class="text-muted d-block">{{ fmt_date($patient->date_of_birth) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                @if ($patient->phone)
                                    <span><i class="bi bi-telephone me-1 text-muted"></i>{{ $patient->phone }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $patient->status === 'active' ? 'bg-success' : ($patient->status === 'inactive' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    <span class="status-dot"></span>{{ ucfirst($patient->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('patient.view')
                                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('patient.edit')
                                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('patient.delete')
                                    <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button onclick="return confirm('Archive this patient?')" class="btn btn-sm btn-outline-danger" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No patients found</div>
                                <small>Try adjusting your search or filters.</small>
                                @can('patient.create')
                                    <div class="mt-3">
                                        <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Register First Patient
                                        </a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($patients->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $patients->firstItem() }}&ndash;{{ $patients->lastItem() }} of {{ $patients->total() }}
            </small>
            <div>{{ $patients->links() }}</div>
        </div>
    @endif
</x-auth-layout>