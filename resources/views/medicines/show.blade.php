<x-auth-layout>
    <x-page-header title="{{ $medicine->name }}" subtitle="{{ $medicine->generic_name ?? 'Medicine details' }}"
        :breadcrumbs="[['label' => 'Medicines', 'url' => route('medicines.index')], ['label' => $medicine->name]]">
        @can('inventory.stock_in')
            <a href="{{ route('inventory.stock-in.form', $medicine) }}" class="btn btn-success btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-circle me-1"></i> Stock In
            </a>
        @endcan
        @can('inventory.stock_out')
            <a href="{{ route('inventory.stock-out.form', $medicine) }}" class="btn btn-warning btn-sm d-inline-flex align-items-center">
                <i class="bi bi-dash-circle me-1"></i> Stock Out
            </a>
        @endcan
        @can('inventory.adjust')
            <a href="{{ route('inventory.adjust.form', $medicine) }}" class="btn btn-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-gear me-1"></i> Adjust
            </a>
        @endcan
        @can('medicine.edit')
            <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-capsule me-2"></i>Medicine Information</h6>
                    <span class="badge {{ $medicine->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $medicine->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Name</label>
                            <div class="fw-semibold">{{ $medicine->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Generic Name</label>
                            <div>{{ $medicine->generic_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Manufacturer</label>
                            <div>{{ $medicine->manufacturer ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Category</label>
                            <div>{{ $medicine->category ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Form</label>
                            <div>{{ $medicine->form ? ucfirst($medicine->form) : '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Strength</label>
                            <div>{{ $medicine->strength ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Unit</label>
                            <div>{{ $medicine->unit ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if (isset($movements) && $movements->count())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Stock Movements</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Balance</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movements as $m)
                                    <tr>
                                        <td>{{ fmt_date($m->movement_date) }}</td>
                                        <td><span class="badge bg-{{ match($m->type) { 'opening'=>'info', 'stock_in'=>'success', 'stock_out'=>'warning', default=>'secondary' } }}">{{ $m->type_label }}</span></td>
                                        <td>{{ $m->quantity > 0 ? '+' . $m->quantity : $m->quantity }}</td>
                                        <td>{{ $m->balance_after }}</td>
                                        <td>{{ $m->performer->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-tag me-2"></i>Pricing & Stock</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label text-muted small">Unit Price</label>
                        <div class="fw-bold fs-5">${{ number_format($medicine->unit_price, 2) }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small">Cost Price</label>
                        <div>${{ number_format($medicine->cost_price, 2) }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small">Selling Price</label>
                        <div>${{ number_format($medicine->selling_price, 2) }}</div>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <label class="form-label text-muted small">Stock Quantity</label>
                        <div>
                            @if ($medicine->stock_quantity <= 0)
                                <span class="badge bg-danger fs-6">Out of stock</span>
                            @elseif ($medicine->isLowStock())
                                <span class="badge bg-warning text-dark fs-6">Low stock ({{ $medicine->stock_quantity }})</span>
                            @else
                                <span class="badge bg-success fs-6">{{ $medicine->stock_quantity }} units</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small">Minimum Stock Level</label>
                        <div>{{ $medicine->minimum_stock_level }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small">Expiry Date</label>
                        <div>
                            @if ($medicine->expiry_date)
                                @if ($medicine->isExpired())
                                    <span class="badge bg-danger">Expired {{ fmt_date($medicine->expiry_date) }}</span>
                                @elseif ($medicine->isExpiringSoon())
                                    <span class="badge bg-warning text-dark">Expiring {{ fmt_date($medicine->expiry_date) }}</span>
                                @else
                                    {{ fmt_date($medicine->expiry_date) }}
                                @endif
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($medicine->notes)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-text-left me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $medicine->notes }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>
