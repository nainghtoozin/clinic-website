<x-auth-layout>
    <x-page-header title="Medicines" subtitle="Pharmacy formulary and stock levels"
        :breadcrumbs="[['label' => 'Medicines']]">
        @can('medicine.create')
            <a href="{{ route('medicines.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-circle me-1"></i> Add Medicine
            </a>
        @endcan
    </x-page-header>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('medicines.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search by name, generic name, category..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="is_active" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'category', 'is_active']))
                            <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
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
        @if (request()->hasAny(['search', 'category', 'is_active']))
            <a href="{{ route('medicines.index') }}" class="small text-decoration-none">
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
                        <th>Name</th>
                        <th class="d-none d-md-table-cell">Generic Name</th>
                        <th class="d-none d-lg-table-cell">Category</th>
                        <th class="d-none d-lg-table-cell">Strength</th>
                        <th class="text-end">Price</th>
                        <th>Stock</th>
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
                                        <div class="fw-medium text-truncate">{{ $medicine->name }}</div>
                                        @if ($medicine->form)
                                            <small class="text-muted">{{ ucfirst($medicine->form) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $medicine->generic_name ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell">
                                @if ($medicine->category)
                                    <span class="badge bg-primary-subtle text-primary">{{ $medicine->category }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $medicine->strength ?? '-' }}</td>
                            <td class="text-end fw-semibold">${{ number_format($medicine->unit_price, 2) }}</td>
                            <td>
                                @php
                                    $stockClass = match ($medicine->stock_status) {
                                        'expired' => 'bg-danger',
                                        'expiring' => 'bg-warning text-dark',
                                        'low' => 'bg-warning text-dark',
                                        default => 'bg-success',
                                    };
                                    $stockLabel = match ($medicine->stock_status) {
                                        'expired' => 'Expired',
                                        'expiring' => 'Expiring soon',
                                        'low' => 'Low stock (' . $medicine->stock_quantity . ')',
                                        default => $medicine->stock_quantity,
                                    };
                                @endphp
                                @if ($medicine->stock_quantity <= 0)
                                    <span class="badge bg-danger">Out of stock</span>
                                @else
                                    <span class="badge {{ $stockClass }}">{{ $stockLabel }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $medicine->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    <span class="status-dot"></span>{{ $medicine->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('medicine.edit')
                                        <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-capsule fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No medicines found</div>
                                <small>Try adjusting your search or filters.</small>
                                @can('medicine.create')
                                    <div class="mt-3">
                                        <a href="{{ route('medicines.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Add First Medicine
                                        </a>
                                    </div>
                                @endcan
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
