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

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Create Doctor</h5>
            </div>

            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" action="{{ route('doctors.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Name</label>
                            <input name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label>Title</label>
                            <input name="title" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Profile Image</label>
                            <input type="file" name="profile_image" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Experience (Years)</label>
                            <input type="number" name="experience_years" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label>Short Description</label>
                            <textarea name="short_description" class="form-control"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label>Biography</label>
                            <textarea name="biography" rows="4" class="form-control"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label>Departments</label>
                            <select name="departments[]" class="form-select" multiple>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Locations</label>
                            <select name="locations[]" class="form-select" multiple>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="board_certified">
                                <label class="form-check-label">Board Certified</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_available" checked>
                                <label class="form-check-label">Available</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button class="btn btn-primary">Save Doctor</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>

</html>
