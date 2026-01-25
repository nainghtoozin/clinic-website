<x-auth-layout>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">📍 Locations</h4>
            @can('location.create')
                <a href="{{ route('locations.create') }}" class="btn btn-primary">
                    + Add Location
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $location)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $location->name }}</td>
                                <td class="text-muted">{{ $location->slug }}</td>
                                <td>{{ Str::limit($location->description, 40) }}</td>
                                <td>
                                    @can('location.edit')
                                        <a href="{{ route('locations.edit', $location) }}"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                    @endcan

                                    @can('location.delete')
                                        <form action="{{ route('locations.destroy', $location) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Delete this location?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No locations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $locations->links() }}
        </div>
    </div>
</x-auth-layout>
