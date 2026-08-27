<x-auth-layout>
    <x-page-header title="Edit Lab Test" subtitle="{{ $labTest->name }}"
        :breadcrumbs="[['label' => 'Lab Tests', 'url' => route('lab-tests.index')], ['label' => $labTest->name, 'url' => route('lab-tests.show', $labTest)], ['label' => 'Edit']]">
        <a href="{{ route('lab-tests.show', $labTest) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-2" role="alert">
            <i class="bi bi-exclamation-octagon fs-5 mt-1"></i>
            <div>
                <strong class="d-block mb-1">Please fix the following errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="edit-form" method="POST" action="{{ route('lab-tests.update', $labTest) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-eyedropper me-2"></i>Test Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $labTest->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $labTest->code) }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $labTest->category) }}">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sample Type</label>
                                <input type="text" name="sample_type" class="form-control @error('sample_type') is-invalid @enderror" value="{{ old('sample_type', $labTest->sample_type) }}">
                                @error('sample_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $labTest->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-tag me-2"></i>Pricing & Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $labTest->price) }}" step="0.01" min="0" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $labTest->unit) }}">
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reference Range</label>
                            <input type="text" name="reference_range" class="form-control @error('reference_range') is-invalid @enderror" value="{{ old('reference_range', $labTest->reference_range) }}">
                            @error('reference_range')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $labTest->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            @can('lab_test.delete')
                <form method="POST" action="{{ route('lab-tests.destroy', $labTest) }}" onsubmit="return confirm('Delete this lab test? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            @endcan
            <div class="d-flex gap-2">
                <a href="{{ route('lab-tests.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Update Lab Test
                </button>
            </div>
        </div>
    </form>
</x-auth-layout>
