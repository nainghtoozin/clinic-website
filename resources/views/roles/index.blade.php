<x-auth-layout>
    <x-page-header title="Roles & Permissions" subtitle="Manage roles and their permission sets"
        :breadcrumbs="[['label' => 'Roles & Permissions']]">
        @can('role.create')
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> Create Role
            </a>
        @endcan
    </x-page-header>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-shield-lock me-1"></i>{{ $roles->count() }} role(s)
        </small>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Role</th>
                        <th class="text-end">Permissions</th>
                        <th class="text-end d-none d-md-table-cell">Users</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar {{ $role->name === 'super-admin' ? 'bg-danger' : ($role->name === 'admin' ? 'bg-primary' : 'bg-success') }}">
                                        <i class="bi bi-person-badge"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ ucfirst($role->name) }}</div>
                                        <small class="text-muted">{{ $role->name }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="bi bi-shield-check me-1"></i>{{ $role->permissions->count() }}
                                </span>
                            </td>
                            <td class="text-end d-none d-md-table-cell">
                                <span class="badge bg-secondary-subtle text-secondary">
                                    <i class="bi bi-people me-1"></i>{{ $role->users_count ?? 0 }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('role.edit')
                                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('role.delete')
                                        @if ($role->name !== 'super-admin')
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete"
                                                    onclick="return confirm('Delete this role?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-shield-lock fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No roles found</div>
                                @can('role.create')
                                    <div class="mt-3">
                                        <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Create First Role
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
</x-auth-layout>
