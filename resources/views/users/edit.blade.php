<!DOCTYPE html>
<html>

<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning">
                        Edit User
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('users.update', $user->id) }}">
                            @csrf @method('PUT')

                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $user->name }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label>Password (optional)</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label>Roles</label>
                                <select name="roles[]" class="form-select" multiple required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}"
                                            {{ in_array($role, $userRoles) ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="text-end">
                                <button class="btn btn-warning">Update</button>
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>
