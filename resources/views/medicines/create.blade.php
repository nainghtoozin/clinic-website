<x-auth-layout>
    <x-page-header title="Add Medicine" subtitle="Register a new medicine in the formulary"
        :breadcrumbs="[['label' => 'Medicines', 'url' => route('medicines.index')], ['label' => 'Add Medicine']]">
        <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Medicines
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

    <form method="POST" action="{{ route('medicines.store') }}" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-capsule me-2"></i>Medicine Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Generic Name</label>
                                <input type="text" name="generic_name" class="form-control @error('generic_name') is-invalid @enderror" value="{{ old('generic_name') }}">
                                @error('generic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Manufacturer</label>
                                <input type="text" name="manufacturer" class="form-control @error('manufacturer') is-invalid @enderror" value="{{ old('manufacturer') }}">
                                @error('manufacturer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category') }}">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Form</label>
                                <select name="form" class="form-select @error('form') is-invalid @enderror">
                                    <option value="">Select form</option>
                                    @foreach (['tablet', 'capsule', 'syrup', 'injection', 'cream', 'ointment', 'drops', 'inhaler', 'patch', 'gel', 'powder', 'suspension'] as $form)
                                        <option value="{{ $form }}" {{ old('form') === $form ? 'selected' : '' }}>{{ ucfirst($form) }}</option>
                                    @endforeach
                                </select>
                                @error('form')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Strength</label>
                                <input type="text" name="strength" class="form-control @error('strength') is-invalid @enderror" value="{{ old('strength') }}" placeholder="e.g., 500mg, 10ml">
                                @error('strength')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unit</label>
                                <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit') }}" placeholder="e.g., tablet, ml, mg">
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
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
                        <h6 class="mb-0"><i class="bi bi-tag me-2"></i>Pricing & Stock</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" name="unit_price" class="form-control @error('unit_price') is-invalid @enderror" value="{{ old('unit_price', '0.00') }}" step="0.01" min="0" required>
                            @error('unit_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Cost Price</label>
                                <input type="number" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price') }}" step="0.01" min="0">
                                @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">Selling Price</label>
                                <input type="number" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price') }}" step="0.01" min="0">
                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', '0') }}" min="0" required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Minimum Stock Level</label>
                            <input type="number" name="minimum_stock_level" class="form-control @error('minimum_stock_level') is-invalid @enderror" value="{{ old('minimum_stock_level', '10') }}" min="0">
                            @error('minimum_stock_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}">
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Create Medicine
            </button>
        </div>
    </form>
</x-auth-layout>
