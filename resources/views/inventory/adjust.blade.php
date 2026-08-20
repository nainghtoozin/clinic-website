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
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Record Batch Adjustment</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.adjust', $medicine) }}" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Batch <span class="text-danger">*</span></label>
                                @if ($batches->isEmpty())
                                    <div class="alert alert-warning py-2 mb-2">
                                        <i class="bi bi-exclamation-triangle me-1"></i> No in-stock batches to adjust. Add stock first.
                                    </div>
                                @else
                                    <select name="inventory_batch_id" class="form-select @error('inventory_batch_id') is-invalid @enderror" required>
                                        <option value="">Select a batch...</option>
                                        @foreach ($batches as $batch)
                                            <option value="{{ $batch->id }}" {{ old('inventory_batch_id') == $batch->id ? 'selected' : '' }}>
                                                {{ $batch->batch_number }} &middot; Qty: {{ $batch->quantity }} &middot;
                                                {{ $batch->expiry_date ? 'Exp ' . fmt_date($batch->expiry_date) : 'No expiry' }}
                                                &middot; {{ $batch->expiry_status_label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Only batches with remaining quantity are listed. Each batch is adjusted independently.</div>
                                @endif
                                @error('inventory_batch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
                                    placeholder="e.g., Stock count correction, Damaged, Expired, Lost" value="{{ old('reason') }}" required>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">For expired stock, use the dedicated "Write Off" action on the medicine page.</div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button class="btn btn-secondary" @disabled($batches->isEmpty())>
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
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Stock Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Medicine</label>
                        <div class="fw-semibold">{{ $medicine->name }}</div>
                        <small class="text-muted">{{ $medicine->generic_name ?? '' }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Total Physical Stock</label>
                        <div class="fw-bold">{{ $medicine->totalPhysicalStock() }} units</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Usable Stock</label>
                        <div class="fs-4 fw-bold text-success">{{ $medicine->usableStockQuantity() }} units</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Expired Stock</label>
                        <div class="fw-semibold text-danger">{{ $medicine->expiredStockQuantity() }} units</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-0">Minimum Level</label>
                        <div>{{ $medicine->minimum_stock_level ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
