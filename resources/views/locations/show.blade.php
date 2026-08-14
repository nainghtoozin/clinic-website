<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Location Details</h5>
        <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Name</label>
                    <div class="fw-semibold">{{ $location->name }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Slug</label>
                    <div>{{ $location->slug }}</div>
                </div>
                @if ($location->description)
                    <div class="col-12">
                        <label class="form-label text-muted small">Description</label>
                        <div>{{ $location->description }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-auth-layout>
