<x-auth-layout>
    <x-page-header title="Doctors" subtitle="Manage doctors and their availability schedules"
        :breadcrumbs="[['label' => 'Doctors']]">
        @can('doctor.create')
            <a href="{{ route('doctors.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> Add Doctor
            </a>
        @endcan
    </x-page-header>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('doctors.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search by doctor name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Location</label>
                        <select name="location_id" class="form-select form-select-sm">
                            <option value="">All Locations</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}"
                                    {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="is_available" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="1" {{ request('is_available') === '1' ? 'selected' : '' }}>Available</option>
                            <option value="0" {{ request('is_available') === '0' ? 'selected' : '' }}>Unavailable</option>
                        </select>
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'department_id', 'location_id', 'is_available']))
                            <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary btn-sm"
                                title="Clear filters">
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
            <i class="bi bi-person-badge me-1"></i>{{ $doctors->total() }} doctor(s)
            @if (request()->has('search') && request('search'))
                @php($searchTerm = request('search'))
                &middot; matching &ldquo;{{ $searchTerm }}&rdquo;
            @endif
        </small>
        @if (request()->hasAny(['search', 'department_id', 'location_id', 'is_available']))
            <a href="{{ route('doctors.index') }}" class="small text-decoration-none">
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
                        <th>Doctor</th>
                        <th class="d-none d-md-table-cell">Department</th>
                        <th class="d-none d-lg-table-cell">Location</th>
                        <th class="d-none d-md-table-cell">Experience</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctors as $doctor)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($doctor->profile_image)
                                        <img src="{{ Storage::url($doctor->profile_image) }}" class="avatar"
                                            alt="{{ $doctor->name }}">
                                    @else
                                        <span class="avatar bg-primary">{{ initials($doctor->name) }}</span>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $doctor->name }}</div>
                                        <small class="text-muted text-truncate d-block" style="max-width:220px;">
                                            {{ $doctor->title ?? $doctor->role ?? '-' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if ($doctor->department)
                                    <span class="badge bg-primary-subtle text-primary">{{ $doctor->department->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                @if ($doctor->location)
                                    <span class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $doctor->location }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if ($doctor->experience_years !== null)
                                    <span>{{ $doctor->experience_years }} yrs</span>
                                    @if ($doctor->board_certified)
                                        <span class="badge bg-success-subtle text-success ms-1">Certified</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $doctor->is_available ? 'bg-success' : 'bg-secondary' }}">
                                    <span class="status-dot"></span>{{ $doctor->is_available ? 'Available' : 'Unavailable' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('doctor.view')
                                    <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-sm btn-outline-primary"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('doctor.edit')
                                    <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-sm btn-outline-warning"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('doctor.delete')
                                    <form method="POST" action="{{ route('doctors.destroy', $doctor) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button onclick="return confirm('Delete this doctor?')"
                                            class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-person-badge fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No doctors found</div>
                                <small>Try adjusting your search or filters.</small>
                                @can('doctor.create')
                                    <div class="mt-3">
                                        <a href="{{ route('doctors.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Add First Doctor
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
    @if ($doctors->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $doctors->firstItem() }}&ndash;{{ $doctors->lastItem() }} of {{ $doctors->total() }}
            </small>
            <div>{{ $doctors->links() }}</div>
        </div>
    @endif
</x-auth-layout>
