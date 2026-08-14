<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Service Details</h5>
        <a href="{{ route('services.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Title</label>
                    <div class="fw-semibold">{{ $service->title }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Category</label>
                    <div>{{ $service->category ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Price</label>
                    <div>{{ $service->price ? '$' . number_format($service->price, 2) : '-' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Status</label>
                    <div>
                        <span class="badge bg-{{ $service->status ? 'success' : 'secondary' }}">
                            {{ $service->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                @if ($service->description)
                    <div class="col-12">
                        <label class="form-label text-muted small">Description</label>
                        <div>{{ $service->description }}</div>
                    </div>
                @endif
                @if ($service->features)
                    <div class="col-12">
                        <label class="form-label text-muted small">Features</label>
                        <div>{{ is_array($service->features) ? implode(', ', $service->features) : $service->features }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-auth-layout>
