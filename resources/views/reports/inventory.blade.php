<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Inventory Report</h4>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted small">Total</h6>
                    <h3>{{ $totalMedicines }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted small">Low Stock</h6>
                    <h3 class="text-warning">{{ $lowStock }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted small">Out of Stock</h6>
                    <h3 class="text-danger">{{ $outOfStock }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted small">Expired</h6>
                    <h3 class="text-danger">{{ $expired }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted small">Expiring Soon</h6>
                    <h3 class="text-info">{{ $expiringSoon }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Filters</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.inventory') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Stock Status</label>
                        <select name="stock_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                            <option value="expired" {{ request('stock_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="expiring" {{ request('stock_status') == 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name/Generic name">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('reports.inventory') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Medicine Stock</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Min Level</th>
                            <th>Expiry</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicines as $medicine)
                            <tr>
                                <td>{{ $medicine->name }}</td>
                                <td>{{ $medicine->category ?? '-' }}</td>
                                <td>{{ $medicine->stock_quantity }}</td>
                                <td>{{ $medicine->minimum_stock_level }}</td>
                                <td>{{ $medicine->expiry_date ? fmt_date($medicine->expiry_date) : '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $medicine->stock_status === 'expired' ? 'danger' : ($medicine->stock_status === 'expiring' ? 'info' : ($medicine->stock_status === 'low' ? 'warning text-dark' : 'success')) }}">
                                        {{ ucfirst($medicine->stock_status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No medicines found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($medicines->hasPages())
        <div class="card-footer bg-white">
            {{ $medicines->links() }}
        </div>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Recent Stock Movements</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Medicine</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Balance After</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMovements as $movement)
                            <tr>
                                <td>{{ fmt_date($movement->movement_date) }}</td>
                                <td>{{ $movement->medicine->name ?? '-' }}</td>
                                <td><span class="badge bg-secondary">{{ $movement->type_label }}</span></td>
                                <td>{{ $movement->quantity }}</td>
                                <td>{{ $movement->balance_after }}</td>
                                <td>{{ Str::limit($movement->reason, 30) ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No recent movements</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-auth-layout>
