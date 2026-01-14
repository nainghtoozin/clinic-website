<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles Management</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Roles & Permissions</h4>
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                + Create Role
            </a>
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
                                    <a href="{{ route('roles.edit', $role->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>

                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure?')">
                                            Delete
                                        </button>
                                    </form>
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

</body>

</html>
