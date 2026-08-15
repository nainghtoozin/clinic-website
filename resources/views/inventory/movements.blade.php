<x-auth-layout>
    <x-page-header title="Stock Movements" subtitle="Complete stock movement history"
        :breadcrumbs="[['label' => 'Inventory'], ['label' => 'Movements']]">
        @include('inventory.partials.nav', ['active' => 'movements'])
    </x-page-header>

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
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['medicine_id', 'type', 'date_from', 'date_to']))
                            <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results summary --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-clock-history me-1"></i>{{ $movements->total() }} movement(s)
        </small>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Medicine</th>
                        <th>Type</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Balance</th>
                        <th class="d-none d-md-table-cell">Reason</th>
                        <th class="d-none d-lg-table-cell">By</th>
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
                                <span class="badge bg-{{ match ($movement->type) {
                                    'opening' => 'info',
                                    'stock_in' => 'success',
                                    'stock_out' => 'warning',
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
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
    @if ($movements->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $movements->firstItem() }}&ndash;{{ $movements->lastItem() }} of {{ $movements->total() }}
            </small>
            <div>{{ $movements->links() }}</div>
        </div>
    @endif
</x-auth-layout>
