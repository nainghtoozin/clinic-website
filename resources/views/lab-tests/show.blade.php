<x-auth-layout>
    <x-page-header title="{{ $labTest->name }}" subtitle="{{ $labTest->code }}"
        :breadcrumbs="[['label' => 'Lab Tests', 'url' => route('lab-tests.index')], ['label' => $labTest->name]]">
        @can('lab_test.edit')
            <a href="{{ route('lab-tests.edit', $labTest) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('lab-tests.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-eyedropper me-2"></i>Test Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Name</label>
                            <div class="fw-semibold">{{ $labTest->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Code</label>
                            <div><span class="badge bg-primary">{{ $labTest->code }}</span></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Category</label>
                            <div>{{ $labTest->category ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Sample Type</label>
                            <div>{{ $labTest->sample_type ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Unit</label>
                            <div>{{ $labTest->unit ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Reference Range</label>
                            <div>{{ $labTest->reference_range ?? '-' }}</div>
                        </div>
                        @if ($labTest->description)
                            <div class="col-12">
                                <label class="form-label text-muted small mb-0">Description</label>
                                <div>{{ $labTest->description }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Price</span>
                        <span class="fw-semibold">${{ number_format($labTest->price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge {{ $labTest->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $labTest->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Investigations</span>
                        <span class="fw-semibold">{{ $labTest->investigations_count }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
