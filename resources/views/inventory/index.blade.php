<x-auth-layout>
    <x-page-header title="Stock List" subtitle="Current stock levels and expiry status"
        :breadcrumbs="[['label' => 'Inventory'], ['label' => 'Stock List']]">
        @include('inventory.partials.nav', ['active' => 'index'])
    </x-page-header>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('inventory.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search by name or generic name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Stock Status</label>
                        <select name="stock_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                            <option value="normal" {{ request('stock_status') === 'normal' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Expiry Status</label>
                        <select name="expiry_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="expired" {{ request('expiry_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="expiring" {{ request('expiry_status') === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                            <option value="normal" {{ request('expiry_status') === 'normal' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'category', 'stock_status', 'expiry_status']))
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
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
            <i class="bi bi-capsule me-1"></i>{{ $medicines->total() }} medicine(s)
            @if (request()->has('search') && request('search'))
                &middot; matching &ldquo;{{ request('search') }}&rdquo;
            @endif
        </small>
        @if (request()->hasAny(['search', 'category', 'stock_status', 'expiry_status']))
            <a href="{{ route('inventory.index') }}" class="small text-decoration-none">
                <i class="bi bi-slash-circle me-1"></i>Clear filters
            </a>
        @endif
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Medicine</th>
                        <th class="d-none d-md-table-cell">Category</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end d-none d-lg-table-cell">Min Level</th>
                        <th class="d-none d-lg-table-cell">Expiry</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($medicines as $medicine)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-primary"><i class="bi bi-capsule"></i></span>
                                    <div class="min-w-0">
                                        <a href="{{ route('medicines.show', $medicine) }}" class="fw-medium text-truncate d-block">{{ $medicine->name }}</a>
                                        @if ($medicine->generic_name)
                                            <small class="text-muted">{{ $medicine->generic_name }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if ($medicine->category)
                                    <span class="badge bg-primary-subtle text-primary">{{ $medicine->category }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="badge {{ $medicine->stock_quantity <= 0 ? 'bg-danger' : 'bg-success' }} fs-6">
                                    {{ $medicine->stock_quantity }}
                                </span>
                                @if ($medicine->stock_quantity <= $medicine->minimum_stock_level)
                                    <span class="d-block mt-1">
                                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Low</span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-end d-none d-lg-table-cell">{{ $medicine->minimum_stock_level ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell">
                                @php
                                    $nearestBatch = $medicine->inventoryBatches->first();
                                    $batchCount = $medicine->inventoryBatches->count();
                                @endphp
                                @if ($nearestBatch && $nearestBatch->expiry_date)
                                    @if ($nearestBatch->isExpired())
                                        <span class="badge bg-danger">{{ fmt_date($nearestBatch->expiry_date) }}</span>
                                    @elseif ($nearestBatch->isExpiringSoon())
                                        <span class="badge bg-warning text-dark">{{ fmt_date($nearestBatch->expiry_date) }}</span>
                                    @else
                                        {{ fmt_date($nearestBatch->expiry_date) }}
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                                @if ($batchCount > 0)
                                    <small class="d-block text-muted">{{ $batchCount }} batch(es)</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ match ($medicine->stock_status) {
                                    'expired' => 'bg-danger',
                                    'expiring' => 'bg-warning text-dark',
                                    'low' => 'bg-warning text-dark',
                                    default => 'bg-success',
                                } }}">
                                    <span class="status-dot"></span>{{ ucfirst(str_replace('_', ' ', $medicine->stock_status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @can('inventory.stock_in')
                                        <a href="{{ route('inventory.stock-in.form', $medicine) }}" class="btn btn-sm btn-outline-success" title="Stock In">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                    @endcan
                                    @can('inventory.stock_out')
                                        <a href="{{ route('inventory.stock-out.form', $medicine) }}" class="btn btn-sm btn-outline-warning" title="Stock Out">
                                            <i class="bi bi-dash-circle"></i>
                                        </a>
                                    @endcan
                                    @can('inventory.adjust')
                                        <a href="{{ route('inventory.adjust.form', $medicine) }}" class="btn btn-sm btn-outline-secondary" title="Adjust">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-capsule fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No medicines found</div>
                                <small>Try adjusting your search or filters.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($medicines->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $medicines->firstItem() }}&ndash;{{ $medicines->lastItem() }} of {{ $medicines->total() }}
            </small>
            <div>{{ $medicines->links() }}</div>
        </div>
    @endif
</x-auth-layout>
