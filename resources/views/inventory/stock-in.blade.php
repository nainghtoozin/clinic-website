<x-auth-layout>
    <x-page-header title="Stock In" subtitle="{{ $medicine->name }}"
        :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.dashboard')], ['label' => 'Stock In']]">
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
                    <h6 class="mb-0"><i class="bi bi-plus-circle me-2 text-success"></i>Record Stock In (New Batch)</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.stock-in', $medicine) }}" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Batch / Lot Number <span class="text-danger">*</span></label>
                                <input type="text" name="batch_number" class="form-control @error('batch_number') is-invalid @enderror"
                                    placeholder="e.g., LOT-2026-001" value="{{ old('batch_number') }}" required>
                                @error('batch_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quantity to Add <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                                    min="1" value="{{ old('quantity', 1) }}" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Received Date <span class="text-danger">*</span></label>
                                <input type="date" name="received_date" class="form-control @error('received_date') is-invalid @enderror"
                                    value="{{ old('received_date', now()->toDateString()) }}" required>
                                @error('received_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
                                    value="{{ old('expiry_date') }}" required>
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unit Cost</label>
                                <input type="number" name="unit_cost" step="0.01" min="0" class="form-control @error('unit_cost') is-invalid @enderror"
                                    placeholder="e.g., 12.50" value="{{ old('unit_cost') }}">
                                @error('unit_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Supplier / Source</label>
                                <input type="text" name="supplier" class="form-control @error('supplier') is-invalid @enderror"
                                    placeholder="e.g., Supplier Name" value="{{ old('supplier') }}">
                                @error('supplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror"
                                    placeholder="e.g., Purchase order, Return" value="{{ old('reason') }}">
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i> Record Stock In
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
                        <label class="form-label text-muted small mb-0">Usable Stock</label>
                        <div class="fs-4 fw-bold">{{ $medicine->usableStockQuantity() }} units</div>
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
