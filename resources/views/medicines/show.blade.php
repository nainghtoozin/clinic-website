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

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Stock by Batch / Lot</h6>
                    <span class="text-muted small">
                        Total: {{ $medicine->totalPhysicalStock() }} &middot; Usable: <span class="text-success fw-medium">{{ $medicine->usableStockQuantity() }}</span> &middot; Expired: <span class="text-danger fw-medium">{{ $medicine->expiredStockQuantity() }}</span>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Batch / Lot</th>
                                <th>Received</th>
                                <th>Expiry</th>
                                <th class="text-end">Qty</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($batches as $batch)
                                <tr>
                                    <td class="fw-medium">{{ $batch->batch_number }}</td>
                                    <td>{{ fmt_date($batch->received_date) }}</td>
                                    <td>{{ $batch->expiry_date ? fmt_date($batch->expiry_date) : '-' }}</td>
                                    <td class="text-end fw-semibold">{{ $batch->quantity }}</td>
                                    <td>
                                        <span class="badge {{ $batch->status_badge }}">
                                            @if ($batch->expiry_status === 'expired')
                                                <i class="bi bi-x-circle me-1"></i>
                                            @endif
                                            {{ $batch->expiry_status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if ($batch->isExpired() && $batch->quantity > 0 && auth()->user()->can('inventory.adjust'))
                                            <form method="POST" action="{{ route('inventory.batch.expire', $batch) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Write off {{ $batch->quantity }} units from expired batch {{ $batch->batch_number }}?');">
                                                    <i class="bi bi-x-lg me-1"></i> Write Off
                                                </button>
                                            </form>
                                        @elseif ($batch->canDelete() && auth()->user()->can('inventory.adjust'))
                                            <button type="button" class="btn btn-sm btn-outline-danger" x-data
                                                @click="window.dispatchEvent(new CustomEvent('open-batch-delete', { detail: {
                                                    id: {{ $batch->id }},
                                                    medicine: {{ Js::from($medicine->name) }},
                                                    batch: {{ Js::from($batch->batch_number) }},
                                                    quantity: {{ $batch->quantity }},
                                                    expiry: {{ Js::from($batch->expiry_date ? $batch->expiry_date->format('M d, Y') : null) }}
                                                } }))">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary"
                                                title="{{ $batch->deleteBlockReason() ?: 'Batch has no stock remaining' }}">
                                                <i class="bi bi-lock me-1"></i> Protected
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                                        No batches recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                                    <th>Batch</th>
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
                                        <td>
                                            @if ($m->inventoryBatch)
                                                <span class="badge bg-light border text-dark">{{ $m->inventoryBatch->batch_number }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-{{ match($m->type) { 'opening'=>'info', 'stock_in'=>'success', 'stock_out'=>'warning', 'dispensed'=>'primary', 'expired'=>'danger', default=>'secondary' } }}">{{ $m->type_label }}</span></td>
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

    {{-- Batch delete confirmation modal --}}
    <div x-data="{
        open: false,
        batch: null,
        show(detail) { this.batch = detail; this.open = true; },
        close() { this.open = false; }
    }"
        x-init="$watch('open', (val) => { if (val) document.body.classList.add('modal-open'); else document.body.classList.remove('modal-open'); })"
        @open-batch-delete.window="show($event.detail)"
        @keydown.escape.window="close()">
        <div x-show="open" x-cloak class="modal" tabindex="-1" role="dialog" aria-modal="true"
            :aria-hidden="open ? 'false' : 'true'" @click.self="close()">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title"><i class="bi bi-trash me-2 text-danger"></i>Delete Unused Stock Record</h6>
                        <button type="button" class="btn-close" @click="close()" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <template x-if="batch">
                            <div>
                                <div class="alert alert-warning py-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Deleting an unused stock record cannot be undone. Records with transaction history are never deleted.
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small mb-0">Medicine</label>
                                        <div class="fw-semibold" x-text="batch.medicine"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small mb-0">Batch / Lot</label>
                                        <div class="fw-semibold" x-text="batch.batch"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small mb-0">Current Quantity</label>
                                        <div x-text="batch.quantity"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small mb-0">Expiry Date</label>
                                        <div x-text="batch.expiry || '—'"></div>
                                    </div>
                                </div>
                                <p class="mb-0"><strong>Delete this unused stock record?</strong></p>
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" @click="close()">Cancel</button>
                        <form method="POST" :action="'/inventory/batches/' + (batch ? batch.id : '')">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Delete Stock Record
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div x-show="open" x-cloak class="modal-backdrop fade show"></div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</x-auth-layout>
