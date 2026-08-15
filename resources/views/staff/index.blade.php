<x-auth-layout>
    <x-page-header title="Staff Management" subtitle="Manage clinic staff accounts and roles"
        :breadcrumbs="[['label' => 'Staff']]">
        @can('staff.create')
            <a href="{{ route('staff.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> Add Staff
            </a>
        @endcan
    </x-page-header>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('staff.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Name, email, phone, position..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">All Roles</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="is_active" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'role', 'is_active']))
                            <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary btn-sm"
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
            <i class="bi bi-people me-1"></i>{{ $users->total() }} staff member(s)
            @if (request()->has('search') && request('search'))
                @php($searchTerm = request('search'))
                &middot; matching &ldquo;{{ $searchTerm }}&rdquo;
            @endif
        </small>
        @if (request()->hasAny(['search', 'role', 'is_active']))
            <a href="{{ route('staff.index') }}" class="small text-decoration-none">
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
                        <th>Name</th>
                        <th class="d-none d-lg-table-cell">Email</th>
                        <th class="d-none d-md-table-cell">Phone</th>
                        <th class="d-none d-md-table-cell">Position</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($user->avatar)
                                        <img src="{{ Storage::url($user->avatar) }}" class="avatar"
                                            alt="{{ $user->name }}">
                                    @else
                                        <span class="avatar bg-primary">{{ initials($user->name) }}</span>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $user->name }}</div>
                                        @if ($user->doctor)
                                            <small class="text-primary text-truncate d-block" style="max-width:200px;">
                                                <i class="bi bi-person-badge me-1"></i>{{ $user->doctor->name }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $user->email }}</td>
                            <td class="d-none d-md-table-cell">
                                @if ($user->phone)
                                    <span><i class="bi bi-telephone me-1 text-muted"></i>{{ $user->phone }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">{{ $user->position ?? '—' }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge {{ match ($role->name) {
                                        'super-admin' => 'bg-danger',
                                        'admin' => 'bg-primary',
                                        'doctor' => 'bg-success',
                                        'nurse' => 'bg-info text-dark',
                                        'receptionist' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    } }}">
                                        <span class="status-dot"></span>{{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                                @if ($user->roles->isEmpty())
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    <span class="status-dot"></span>{{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('staff.view')
                                    <a href="{{ route('staff.show', $user) }}" class="btn btn-sm btn-outline-primary"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('staff.edit')
                                    <a href="{{ route('staff.edit', $user) }}" class="btn btn-sm btn-outline-warning"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('staff.toggle-status', $user) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                class="btn btn-sm btn-outline-{{ $user->is_active ? 'secondary' : 'success' }}"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi bi-{{ $user->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                @can('staff.delete')
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('staff.destroy', $user) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Deactivate this staff member?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Deactivate">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No staff members found</div>
                                <small>Try adjusting your search or filters.</small>
                                @can('staff.create')
                                    <div class="mt-3">
                                        <a href="{{ route('staff.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Add First Staff Member
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
    @if ($users->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $users->firstItem() }}&ndash;{{ $users->lastItem() }} of {{ $users->total() }}
            </small>
            <div>{{ $users->links() }}</div>
        </div>
    @endif
</x-auth-layout>