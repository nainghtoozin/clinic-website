<x-auth-layout>
    <div class="container py-5">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Roles & Permissions</h4>
            @can('role.create')
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    + Create Role
                </a>
            @endcan
        </div>

        <!-- Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">

                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Role Name</th>
                            <th>Permissions</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($roles as $index => $role)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td class="fw-semibold">
                                    {{ $role->name }}
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        {{ $role->permissions->count() }} permissions
                                    </span>
                                </td>

                                <td class="text-end">
                                    @can('role.edit')
                                        <a href="{{ route('roles.edit', $role->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>
                                    @endcan


                                    @can('role.delete')
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure?')">
                                                Delete
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No roles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</x-auth-layout>
