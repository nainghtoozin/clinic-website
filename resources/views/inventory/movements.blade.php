<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Stock Movement History</h5>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-primary">Dashboard</a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-primary">Medicine List</a>
            <a href="{{ route('inventory.movements') }}" class="btn btn-primary">Movements</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('inventory.movements') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <select name="medicine_id" class="form-select form-select-sm">
                            <option value="">All Medicines</option>
                            @foreach ($medicines as $id => $name)
                                <option value="{{ $id }}" {{ request('medicine_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="opening" {{ request('type') === 'opening' ? 'selected' : '' }}>Opening</option>
                            <option value="stock_in" {{ request('type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                            <option value="stock_out" {{ request('type') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i> Filter</button>
                    </div>
                    @if (request()->hasAny(['medicine_id', 'type', 'date_from', 'date_to']))
                        <div class="col-auto">
                            <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
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
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr>
                                <td>{{ fmt_date($movement->movement_date) }}</td>
                                <td>{{ $movement->medicine->name ?? 'Deleted' }}</td>
                                <td>
                                    <span class="badge bg-{{ match($movement->type) {
                                        'opening' => 'info',
                                        'stock_in' => 'success',
                                        'stock_out' => 'warning',
                                        'adjustment' => 'secondary',
                                        default => 'secondary'
                                    } }}">
                                        {{ $movement->type_label }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if ($movement->quantity > 0)
                                        <span class="text-success">+{{ $movement->quantity }}</span>
                                    @else
                                        <span class="text-danger">{{ $movement->quantity }}</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ $movement->balance_after }}</td>
                                <td class="d-none d-md-table-cell">{{ $movement->reason ?? '—' }}</td>
                                <td>{{ $movement->performer->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                                    No movements found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $movements->links() }}</div>
</x-auth-layout>
