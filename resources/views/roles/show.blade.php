<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Role Details</h5>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0">{{ $role->name }}</h6>
        </div>
        <div class="card-body">
            <label class="form-label text-muted small">Permissions</label>
            <div class="d-flex flex-wrap gap-1">
                @forelse ($role->permissions as $permission)
                    <span class="badge bg-info text-dark">{{ $permission->name }}</span>
                @empty
                    <span class="text-muted">No permissions assigned</span>
                @endforelse
            </div>
        </div>
    </div>
</x-auth-layout>
