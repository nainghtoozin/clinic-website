<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Patients</h5>
        @can('patient.create')
            <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Patient
            </a>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('patients.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, number, phone, or email..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="gender" class="form-select form-select-sm">
                            <option value="">All Gender</option>
                            <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ request('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'status', 'gender']))
                            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Patient #</th>
                        <th>Name</th>
                        <th class="d-none d-md-table-cell">Phone</th>
                        <th class="d-none d-lg-table-cell">Email</th>
                        <th class="d-none d-md-table-cell">Gender</th>
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
                            <td class="fw-medium">{{ $patient->name }}</td>
                            <td class="d-none d-md-table-cell">{{ $patient->phone ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell">{{ $patient->email ?? '-' }}</td>
                            <td class="d-none d-md-table-cell">{{ ucfirst($patient->gender ?? '-') }}</td>
                            <td>
                                <span class="badge bg-{{ $patient->status === 'active' ? 'success' : ($patient->status === 'inactive' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($patient->status) }}
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
                                <div>No patients found.</div>
                                @can('patient.create')
                                    <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-plus-lg me-1"></i> Register First Patient
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $patients->links() }}
    </div>
</x-auth-layout>
