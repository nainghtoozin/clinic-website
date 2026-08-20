<x-auth-layout>
    <x-page-header title="Expiry Report" subtitle="Batch-level expiry status across the pharmacy"
        :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.dashboard')], ['label' => 'Expiry Report']]">
        @include('inventory.partials.nav', ['active' => 'expiry'])
    </x-page-header>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('inventory.expiry', ['status' => 'expired']) }}"
                class="card stat-card shadow-sm border-0 text-decoration-none h-100 {{ $status === 'expired' ? 'ring' : '' }}">
                <div class="card-body py-3">
                    <div class="stat-label text-danger mb-1"><i class="bi bi-x-octagon me-1"></i>Expired</div>
                    <h4 class="stat-value mb-0 text-danger">{{ $expiredCount }}</h4>
                    <small class="text-muted">batches</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('inventory.expiry', ['status' => 'expiring']) }}"
                class="card stat-card shadow-sm border-0 text-decoration-none h-100 {{ $status === 'expiring' ? 'ring' : '' }}">
                <div class="card-body py-3">
                    <div class="stat-label text-warning mb-1"><i class="bi bi-clock me-1"></i>Expiring Soon</div>
                    <h4 class="stat-value mb-0 text-warning">{{ $expiringCount }}</h4>
                    <small class="text-muted">batches</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('inventory.expiry', ['status' => 'active']) }}"
                class="card stat-card shadow-sm border-0 text-decoration-none h-100 {{ $status === 'active' ? 'ring' : '' }}">
                <div class="card-body py-3">
                    <div class="stat-label text-success mb-1"><i class="bi bi-check-circle me-1"></i>Active</div>
                    <h4 class="stat-value mb-0 text-success">{{ $activeCount }}</h4>
                    <small class="text-muted">batches</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('inventory.expiry', ['status' => 'depleted']) }}"
                class="card stat-card shadow-sm border-0 text-decoration-none h-100 {{ $status === 'depleted' ? 'ring' : '' }}">
                <div class="card-body py-3">
                    <div class="stat-label text-secondary mb-1"><i class="bi bi-box me-1"></i>Depleted</div>
                    <h4 class="stat-value mb-0">{{ $depletedCount }}</h4>
                    <small class="text-muted">batches</small>
                </div>
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('inventory.expiry') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="expiring" {{ $status === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="depleted" {{ $status === 'depleted' ? 'selected' : '' }}>Depleted</option>
                    </select>
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search by medicine or batch / lot..." value="{{ request('search') }}">
                </div>
                <div class="col-auto col-md-3 d-flex gap-2">
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request()->hasAny(['status', 'search']))
                        <a href="{{ route('inventory.expiry') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-box-seam me-1"></i>{{ $batches->total() }} batch(es)
        </small>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Medicine</th>
                        <th>Batch / Lot</th>
                        <th>Received</th>
                        <th>Expiry</th>
                        <th class="text-end">Qty</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $daysAbs = $batch->expiry_date
                                ? abs((int) $batch->expiry_date->diffInDays(now()))
                                : null;
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $batch->medicine->name ?? 'Deleted' }}</span>
                                @if ($batch->medicine?->generic_name)
                                    <small class="text-muted d-block">{{ $batch->medicine->generic_name }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-light border text-dark">{{ $batch->batch_number }}</span></td>
                            <td>{{ $batch->received_date ? fmt_date($batch->received_date) : '-' }}</td>
                            <td>{{ $batch->expiry_date ? fmt_date($batch->expiry_date) : '-' }}</td>
                            <td class="text-end fw-semibold">{{ $batch->quantity }}</td>
                            <td>
                                @if ($batch->expiry_date)
                                    @if ($batch->expiry_status === 'expired')
                                        <span class="text-danger small">{{ $daysAbs }} days expired</span>
                                    @elseif ($daysAbs === 0)
                                        <span class="text-warning small">expires today</span>
                                    @else
                                        <span class="text-muted small">{{ $daysAbs }} days remaining</span>
                                    @endif
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $batch->status_badge }}">
                                    @if ($batch->expiry_status === 'expired')
                                        <i class="bi bi-x-circle me-1"></i>
                                    @endif
                                    {{ $batch->expiry_status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if ($batch->medicine)
                                    <a href="{{ route('medicines.show', $batch->medicine) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-seam me-1"></i> Open
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No batches found</div>
                                <small>Try adjusting your filters.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($batches->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $batches->firstItem() }}&ndash;{{ $batches->lastItem() }} of {{ $batches->total() }}
            </small>
            <div>{{ $batches->links() }}</div>
        </div>
    @endif
</x-auth-layout>
