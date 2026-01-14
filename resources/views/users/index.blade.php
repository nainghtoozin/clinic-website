<!DOCTYPE html>
<html>

<head>
    <title>Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">

        <div class="d-flex justify-content-between mb-4">
            <h4 class="fw-bold">Users Management</h4>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                + Create User
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-end">
                                <a href="{{ route('users.edit', $user->id) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>

                                <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete user?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    </div>
</body>

</html>
