<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Medicine List</h5>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-primary">Dashboard</a>
            <a href="{{ route('inventory.index') }}" class="btn btn-primary">Medicine List</a>
            <a href="{{ route('inventory.movements') }}" class="btn btn-outline-primary">Movements</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('inventory.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="stock_status" class="form-select form-select-sm">
                            <option value="">Stock Status</option>
                            <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                            <option value="normal" {{ request('stock_status') === 'normal' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="expiry_status" class="form-select form-select-sm">
                            <option value="">Expiry Status</option>
                            <option value="expired" {{ request('expiry_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="expiring" {{ request('expiry_status') === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                            <option value="normal" {{ request('expiry_status') === 'normal' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i> Filter</button>
                    </div>
                    @if (request()->hasAny(['search', 'category', 'stock_status', 'expiry_status']))
                        <div class="col-auto">
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm">
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
                            <th>Medicine</th>
                            <th class="d-none d-md-table-cell">Category</th>
                            <th class="d-none d-md-table-cell">Unit</th>
                            <th class="text-end">Stock</th>
                            <th class="text-end d-none d-lg-table-cell">Min Level</th>
                            <th class="d-none d-lg-table-cell">Expiry</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicines as $medicine)
                            <tr>
                                <td>
                                    <strong>{{ $medicine->name }}</strong>
                                    @if ($medicine->generic_name)
                                        <br><small class="text-muted">{{ $medicine->generic_name }}</small>
                                    @endif
                                </td>
                                <td class="d-none d-md-table-cell">{{ $medicine->category ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $medicine->unit ?? '—' }}</td>
                                <td class="text-end fw-bold">{{ $medicine->stock_quantity }}</td>
                                <td class="text-end d-none d-lg-table-cell">{{ $medicine->minimum_stock_level ?? '—' }}</td>
                                <td class="d-none d-lg-table-cell">
                                    @if ($medicine->expiry_date)
                                        {{ fmt_date($medicine->expiry_date) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($medicine->isExpired())
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif ($medicine->isExpiringSoon())
                                        <span class="badge bg-warning text-dark">Expiring</span>
                                    @elseif ($medicine->isLowStock())
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('inventory.stock-in.form', $medicine) }}" class="btn btn-outline-success" title="Stock In">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                        <a href="{{ route('inventory.stock-out.form', $medicine) }}" class="btn btn-outline-warning" title="Stock Out">
                                            <i class="bi bi-dash-circle"></i>
                                        </a>
                                        <a href="{{ route('inventory.adjust.form', $medicine) }}" class="btn btn-outline-secondary" title="Adjust">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-capsule fs-1 d-block mb-2"></i>
                                    No medicines found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $medicines->links() }}</div>
</x-auth-layout>
