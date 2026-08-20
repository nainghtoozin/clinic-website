<x-auth-layout>
    <x-page-header title="Stock Out" subtitle="{{ $medicine->name }}"
        :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.dashboard')], ['label' => 'Stock Out']]">
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
                    <h6 class="mb-0"><i class="bi bi-dash-circle me-2 text-warning"></i>Record Stock Out</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.stock-out', $medicine) }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Select Batch <span class="text-danger">*</span></label>
                            @if ($batches->isEmpty())
                                <div class="alert alert-warning py-2 mb-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i> No usable (non-expired) batches available for this medicine.
                                </div>
                            @else
                                <div class="table-responsive border rounded">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:40px;"></th>
                                                <th>Batch / Lot</th>
                                                <th class="text-end">Available</th>
                                                <th>Expiry</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($batches as $batch)
                                                <tr>
                                                    <td>
                                                        <input class="form-check-input" type="radio" name="inventory_batch_id"
                                                            value="{{ $batch->id }}" @checked($loop->first)
                                                            @if ($batch->isExpired()) disabled @endif>
                                                    </td>
                                                    <td class="fw-medium">{{ $batch->batch_number }}</td>
                                                    <td class="text-end">{{ $batch->quantity }}</td>
                                                    <td>{{ $batch->expiry_date ? fmt_date($batch->expiry_date) : '-' }}</td>
                                                    <td>
                                                        <span class="badge {{ $batch->status_badge }}">
                                                            @if ($batch->isExpired())
                                                                <i class="bi bi-x-circle me-1"></i>Expired &mdash; not dispensible
                                                            @else
                                                                {{ $batch->expiry_status_label }}
                                                            @endif
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="form-text">
                                    Batches are listed FEFO (earliest expiry first). Expired batches cannot be selected.
                                </div>
                            @endif
                            @error('inventory_batch_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quantity to Remove <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                                min="1" value="{{ old('quantity', 1) }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Usable stock available: {{ $medicine->usableStockQuantity() }} units</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror"
                                placeholder="e.g., Damaged, Dispensed, Written off" value="{{ old('reason') }}">
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button class="btn btn-warning" @disabled($batches->isEmpty())>
                                <i class="bi bi-check-lg me-1"></i> Record Stock Out
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
