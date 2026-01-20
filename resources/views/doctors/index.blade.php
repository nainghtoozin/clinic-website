<x-auth-layout>
    <x-page-header title="Doctors">
        <x-slot:actions>
            <a href="{{ route('doctors.create') }}" class="btn btn-primary">+ Add Doctor</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('doctors.index') }}">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">

                    {{-- Search --}}
                    <div class="col-md-3">
                        <label class="form-label">Search Name</label>
                        <input type="text" name="search" class="form-control" placeholder="Doctor name..."
                            value="{{ request('search') }}">
                    </div>

                    {{-- Department --}}
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Location --}}
                    <div class="col-md-3">
                        <label class="form-label">Location</label>
                        <select name="location_id" class="form-select">
                            <option value="">All Locations</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}"
                                    {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-1 d-grid">
                        <button class="btn btn-primary">
                            Filter
                        </button>
                    </div>

                </div>

                {{-- Reset --}}
                @if (request()->hasAny(['search', 'department_id', 'location_id', 'status']))
                    <div class="mt-3">
                        <a href="{{ route('doctors.index') }}" class="btn btn-sm btn-outline-secondary">
                            Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </form>


    @if (session('success'))
        <div class="alert alert-info">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-hover bg-white shadow-sm">
        <thead>
            <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Experience</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($doctors as $doctor)
                <tr>
                    <td class="fw-semibold">{{ $doctor->name }}</td>
                    <td>{{ $doctor->department?->name }}</td>
                    <td>{{ $doctor->experience_years }} yrs</td>
                    <td>
                        <span class="badge {{ $doctor->is_available ? 'bg-success' : 'bg-secondary' }}">
                            {{ $doctor->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                            data-bs-target="#viewDoctor{{ $doctor->id }}">
                            View
                        </button>
                        <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form method="POST" action="{{ route('doctors.destroy', $doctor) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Delete?')" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>

                @include('doctors.show')
            @endforeach
        </tbody>
    </table>

    {{ $doctors->links() }}
</x-auth-layout>
