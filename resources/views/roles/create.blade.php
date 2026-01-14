<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Clinic Website') }}</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Create New Role</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('roles.store') }}">
                            @csrf

                            <!-- Role Name -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Role Name</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Doctor, Nurse, Receptionist" required>
                            </div>

                            <!-- Permissions -->
                            @foreach ($permissions as $group => $items)
                                <div class="card mb-3 border">
                                    <div class="card-header bg-light fw-semibold text-uppercase">
                                        {{ $group }} Permissions
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            @foreach ($items as $permission)
                                                <div class="col-md-4 col-sm-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="permissions[]" value="{{ $permission->name }}"
                                                            id="{{ $permission->name }}">

                                                        <label class="form-check-label" for="{{ $permission->name }}">
                                                            {{ str_replace($group . '.', '', $permission->name) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Submit -->
                            <div class="text-end mt-4">
                                <button class="btn btn-primary px-4">
                                    Create Role
                                </button>
                                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>
