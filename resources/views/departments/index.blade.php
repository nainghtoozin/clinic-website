<x-auth-layout>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">🏥 Departments</h4>
            <a href="{{ route('departments.create') }}" class="btn btn-primary">
                + Add Department
            </a>
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
                            <th>Department</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($department->icon)
                                            <i class="{{ $department->icon }} text-primary"></i>
                                        @endif
                                        <strong>{{ $department->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $department->category ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $department->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $department->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </td>
                                <td>{{ $department->sort_order }}</td>
                                <td>
                                    <a href="{{ route('departments.edit', $department) }}"
                                        class="btn btn-sm btn-outline-primary">Edit</a>

                                    <form action="{{ route('departments.destroy', $department) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Delete this department?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No departments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $departments->links() }}
        </div>
    </div>
</x-auth-layout>
