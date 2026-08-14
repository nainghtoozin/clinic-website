<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Medicines</h4>
            <small class="text-muted">{{ $medicines->total() }} medicines</small>
        </div>
        @can('medicine.create')
            <a href="{{ route('medicines.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add Medicine
            </a>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, generic name, category..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="is_active" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($medicines->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-capsule fs-1 d-block mb-2"></i>
                    No medicines found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Generic Name</th>
                                <th>Category</th>
                                <th>Form</th>
                                <th>Strength</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medicines as $medicine)
                                <tr>
                                    <td class="fw-semibold">{{ $medicine->name }}</td>
                                    <td>{{ $medicine->generic_name ?? '-' }}</td>
                                    <td>
                                        @if ($medicine->category)
                                            <span class="badge bg-secondary">{{ $medicine->category }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $medicine->form ?? '-' }}</td>
                                    <td>{{ $medicine->strength ?? '-' }}</td>
                                    <td>${{ number_format($medicine->unit_price, 2) }}</td>
                                    <td>
                                        @if ($medicine->stock_quantity <= 0)
                                            <span class="badge bg-danger">Out of stock</span>
                                        @elseif ($medicine->stock_quantity <= 10)
                                            <span class="badge bg-warning text-dark">Low stock</span>
                                        @else
                                            <span class="badge bg-success">{{ $medicine->stock_quantity }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $medicine->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $medicine->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('medicine.edit')
                                                <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $medicines->links() }}
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>