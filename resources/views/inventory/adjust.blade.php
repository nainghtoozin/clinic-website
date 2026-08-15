<x-auth-layout>
    <x-page-header title="Stock Adjustment" subtitle="{{ $medicine->name }}"
        :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.dashboard')], ['label' => 'Adjustment']]">
        <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
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

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Record Adjustment</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.adjust', $medicine) }}" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Direction <span class="text-danger">*</span></label>
                                <select name="direction" class="form-select @error('direction') is-invalid @enderror" required>
                                    <option value="increase" {{ old('direction') === 'increase' ? 'selected' : '' }}>Increase (+)</option>
                                    <option value="decrease" {{ old('direction') === 'decrease' ? 'selected' : '' }}>Decrease (&minus;)</option>
                                </select>
                                @error('direction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                                    min="1" value="{{ old('quantity', 1) }}" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Reason <span class="text-danger">*</span></label>
                                <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror"
                                    placeholder="e.g., Stock count correction, Damaged" value="{{ old('reason') }}" required>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button class="btn btn-secondary">
                                <i class="bi bi-check-lg me-1"></i> Save Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Current Stock</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Medicine</label>
                        <div class="fw-semibold">{{ $medicine->name }}</div>
                        <small class="text-muted">{{ $medicine->generic_name ?? '' }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Current Stock</label>
                        <div class="fs-4 fw-bold">{{ $medicine->stock_quantity }} units</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Minimum Level</label>
                        <div>{{ $medicine->minimum_stock_level ?? 0 }}</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-0">Stock Status</label>
                        <div>
                            <span class="badge {{ match ($medicine->stock_status) {
                                'expired' => 'bg-danger',
                                'expiring' => 'bg-warning text-dark',
                                'low' => 'bg-warning text-dark',
                                default => 'bg-success',
                            } }}">
                                <span class="status-dot"></span>{{ ucfirst(str_replace('_', ' ', $medicine->stock_status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
