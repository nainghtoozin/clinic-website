<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Department Details</h5>
        <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Name</label>
                    <div class="fw-semibold">{{ $department->name }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Slug</label>
                    <div>{{ $department->slug }}</div>
                </div>
                @if ($department->category)
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Category</label>
                        <div>{{ $department->category }}</div>
                    </div>
                @endif
                @if ($department->description)
                    <div class="col-12">
                        <label class="form-label text-muted small">Description</label>
                        <div>{{ $department->description }}</div>
                    </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label text-muted small">Status</label>
                    <div>
                        <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Sort Order</label>
                    <div>{{ $department->sort_order ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
