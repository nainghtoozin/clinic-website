<x-auth-layout>
    <x-page-header title="Inventory Dashboard" subtitle="Stock overview across the pharmacy"
        :breadcrumbs="[['label' => 'Inventory']]">
        @include('inventory.partials.nav', ['active' => 'dashboard'])
    </x-page-header>

    {{-- KPI cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Total Medicines</div>
                            <h4 class="stat-value mb-0">{{ $totalMedicines }}</h4>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="bi bi-capsule text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Stock Value</div>
                            <h5 class="stat-value mb-0">${{ number_format($totalStockValue, 2) }}</h5>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="bi bi-cash-stack text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Low Stock</div>
                            <h5 class="stat-value mb-0 {{ $lowStock > 0 ? 'text-warning' : '' }}">{{ $lowStock }}</h5>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label text-muted mb-1">Expired / Expiring</div>
                            <h5 class="stat-value mb-0 {{ $expired + $expiringSoon > 0 ? 'text-danger' : '' }}">{{ $expired + $expiringSoon }}</h5>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10">
                            <i class="bi bi-clock-history text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Low stock --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock Medicines</h6>
                    <a href="{{ route('inventory.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body p-0">
                    @forelse ($lowStockMedicines as $medicine)
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <div class="min-w-0">
                                <a href="{{ route('medicines.show', $medicine) }}" class="fw-medium text-truncate d-block">{{ $medicine->name }}</a>
                                <small class="text-muted">Min level: {{ $medicine->minimum_stock_level ?? 0 }}</small>
                            </div>
                            <span class="badge {{ $medicine->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ $medicine->stock_quantity <= 0 ? 'Out of stock' : $medicine->stock_quantity . ' left' }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                            <small>All medicines are sufficiently stocked</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Expiring soon --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0"><i class="bi bi-clock text-info me-2"></i>Expiring Soon</h6>
                    <a href="{{ route('inventory.index', ['expiry_status' => 'expiring']) }}" class="btn btn-sm btn-outline-info">View All</a>
                </div>
                <div class="card-body p-0">
                    @forelse ($expiringMedicines as $medicine)
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <div class="min-w-0">
                                <a href="{{ route('medicines.show', $medicine) }}" class="fw-medium text-truncate d-block">{{ $medicine->name }}</a>
                                <small class="text-muted">Stock: {{ $medicine->stock_quantity }}</small>
                            </div>
                            <span class="badge bg-info text-dark">{{ fmt_date($medicine->expiry_date) }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-calendar-check fs-3 d-block mb-2 text-success"></i>
                            <small>No medicines expiring in the next 30 days</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Expired --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0"><i class="bi bi-x-octagon text-danger me-2"></i>Expired</h6>
                    <a href="{{ route('inventory.index', ['expiry_status' => 'expired']) }}" class="btn btn-sm btn-outline-danger">View All</a>
                </div>
                <div class="card-body p-0">
                    @forelse ($expiredMedicines as $medicine)
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <div class="min-w-0">
                                <a href="{{ route('medicines.show', $medicine) }}" class="fw-medium text-truncate d-block">{{ $medicine->name }}</a>
                                <small class="text-muted">Stock: {{ $medicine->stock_quantity }}</small>
                            </div>
                            <span class="badge bg-danger">{{ fmt_date($medicine->expiry_date) }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                            <small>No expired medicines</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Recent movements --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Stock Movements</h6>
            <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
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
                            <th class="d-none d-md-table-cell">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentMovements as $movement)
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
                                <td class="d-none d-md-table-cell">{{ $movement->performer->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                                    No stock movements recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-auth-layout>
