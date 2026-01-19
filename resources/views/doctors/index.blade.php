<x-auth-layout>
    <x-page-header title="Doctors">
        <x-slot:actions>
            <a href="{{ route('doctors.create') }}" class="btn btn-primary">+ Add Doctor</a>
        </x-slot:actions>
    </x-page-header>

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
