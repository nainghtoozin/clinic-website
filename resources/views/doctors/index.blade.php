<!doctype html>
<html>

<head>
    <title>Doctors Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('doctors.index') }}">Doctors</a>
            <a href="{{ route('doctors.create') }}" class="btn btn-light btn-sm">+ Add Doctor</a>
        </div>
    </nav>

    <div class="container">

        <div class="row mb-4">
            <div class="col-md-8">
                <form class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search doctors">
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <div class="row g-4">
            @foreach ($doctors as $doctor)
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <img src="{{ asset('storage/' . $doctor->profile_image) }}" class="card-img-top"
                            style="height:220px;object-fit:cover">

                        <div class="card-body">
                            <h5 class="mb-1">{{ $doctor->name }}</h5>
                            <small class="text-muted">{{ $doctor->title }}</small>

                            <p class="mt-2 small">
                                {{ Str::limit($doctor->short_description, 80) }}
                            </p>

                            <span class="badge bg-info">
                                {{ $doctor->primary_department }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $doctors->links() }}
        </div>


    </div>

</body>

</html>
