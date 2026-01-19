<x-auth-layout>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Departments</h5>
            <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">
                + Add Department
            </a>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $department->name }}</td>
                            <td class="text-muted">{{ $department->slug }}</td>
                            <td class="text-end">
                                <a href="{{ route('departments.edit', $department) }}"
                                    class="btn btn-sm btn-outline-warning">
                                    Edit
                                </a>
                                <form action="{{ route('departments.destroy', $department) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Are you sure?')"
                                        class="btn btn-sm btn-outline-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No departments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white">
            {{ $departments->links() }}
        </div>
    </div>
</x-auth-layout>
