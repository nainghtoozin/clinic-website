<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Inventory Dashboard</h5>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-primary">Dashboard</a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-primary">Medicine List</a>
            <a href="{{ route('inventory.movements') }}" class="btn btn-outline-primary">Movements</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="bi bi-capsule text-primary fs-5"></i>
                </div>
                <div class="fs-3 fw-bold text-primary">{{ $totalMedicines }}</div>
                <div class="text-muted small">Total Medicines</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
                </div>
                <div class="fs-3 fw-bold text-warning">{{ $lowStock }}</div>
                <div class="text-muted small">Low Stock</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="bi bi-x-circle text-danger fs-5"></i>
                </div>
                <div class="fs-3 fw-bold text-danger">{{ $outOfStock }}</div>
                <div class="text-muted small">Out of Stock</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="bi bi-clock-history text-secondary fs-5"></i>
                </div>
                <div class="fs-3 fw-bold text-secondary">{{ $expired + $expiringSoon }}</div>
                <div class="text-muted small">Expired / Expiring</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Stock Movements</h6>
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
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentMovements as $movement)
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
                                <td>{{ $movement->performer->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                                    No recent movements.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-auth-layout>
