<x-auth-layout>
    <x-page-header title="Stock Movements" subtitle="Complete stock movement history"
        :breadcrumbs="[['label' => 'Inventory'], ['label' => 'Movements']]">
        @include('inventory.partials.nav', ['active' => 'movements'])
    </x-page-header>

    @error('date_range')
        <div class="alert alert-danger py-2 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ $message }}
        </div>
    @enderror

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('inventory.movements') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Medicine</label>
                        <select name="medicine_id" class="form-select form-select-sm">
                            <option value="">All Medicines</option>
                            @foreach ($medicines as $id => $name)
                                <option value="{{ $id }}" {{ request('medicine_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Type</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="opening" {{ request('type') === 'opening' ? 'selected' : '' }}>Opening Stock</option>
                            <option value="stock_in" {{ request('type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                            <option value="stock_out" {{ request('type') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            <option value="dispensed" {{ request('type') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                            <option value="expired" {{ request('type') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Start Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ old('date_from', request('date_from')) }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">End Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ old('date_to', request('date_to')) }}">
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Apply
                        </button>
                        @if (request()->hasAny(['medicine_id', 'type', 'date_from', 'date_to']))
                            <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary btn-sm" title="Reset filters">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results summary + page size --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-clock-history me-1"></i>{{ $movements->total() }} movement(s)
        </small>
        <form method="GET" action="{{ route('inventory.movements') }}" class="d-inline-flex align-items-center gap-2">
            <label class="small text-muted mb-0">Show</label>
            <select name="per_page" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="15" @selected(($perPage ?? 15) === 15)>15</option>
                <option value="30" @selected(($perPage ?? 15) === 30)>30</option>
                <option value="50" @selected(($perPage ?? 15) === 50)>50</option>
            </select>
            <span class="small text-muted">per page</span>
            @foreach (request()->only(['medicine_id', 'type', 'date_from', 'date_to']) as $key => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
        </form>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Medicine</th>
                        <th>Batch / Lot</th>
                        <th>Type</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Balance</th>
                        <th class="d-none d-md-table-cell">Reason</th>
                        <th class="d-none d-lg-table-cell">By</th>
                        <th class="text-end">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td>{{ fmt_date($movement->movement_date) }}</td>
                            <td>
                                <span class="fw-medium">{{ $movement->medicine->name ?? 'Deleted' }}</span>
                            </td>
                            <td>
                                @if ($movement->inventoryBatch)
                                    <span class="badge bg-light border text-dark">{{ $movement->inventoryBatch->batch_number }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ match ($movement->type) {
                                    'opening' => 'info',
                                    'stock_in' => 'success',
                                    'stock_out' => 'warning',
                                    'dispensed' => 'primary',
                                    'expired' => 'danger',
                                    default => 'secondary',
                                } }}">
                                    <span class="status-dot"></span>{{ $movement->type_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if ($movement->quantity > 0)
                                    <span class="text-success fw-semibold">+{{ $movement->quantity }}</span>
                                @else
                                    <span class="text-danger fw-semibold">{{ $movement->quantity }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold">{{ $movement->balance_after }}</td>
                            <td class="d-none d-md-table-cell">{{ $movement->reason ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell">{{ $movement->performer->name ?? 'System' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    x-data
                                    data-movement="{{ json_encode($movement->detail()) }}"
                                    @click="$dispatch('open-movement-detail', JSON.parse($el.dataset.movement))">
                                    <i class="bi bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No movements found</div>
                                <small>Try adjusting your filters.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
        <small class="text-muted">
            Showing {{ $movements->firstItem() ?? 0 }}&ndash;{{ $movements->lastItem() ?? 0 }} of {{ $movements->total() }}
        </small>
        <div>{{ $movements->links() }}</div>
    </div>

    @include('inventory.partials.movement-modal')
</x-auth-layout>
