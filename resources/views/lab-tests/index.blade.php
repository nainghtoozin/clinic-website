<x-auth-layout>
    <x-page-header title="Lab Test Catalog" subtitle="Manage laboratory test catalog"
        :breadcrumbs="[['label' => 'Lab Tests']]">
        @can('lab_test.create')
            <a href="{{ route('lab-tests.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-circle me-1"></i> Add Lab Test
            </a>
        @endcan
    </x-page-header>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('lab-tests.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search by name, code, category..." value="{{ request('search') }}">
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
                            <a href="{{ route('lab-tests.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted d-flex align-items-center">
            <i class="bi bi-eyedropper me-1"></i>{{ $labTests->total() }} test(s)
        </small>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($labTests->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-eyedropper fs-1 text-muted d-block mb-2"></i>
                    <h6 class="text-muted">No Lab Tests Found</h6>
                    <p class="small text-muted mb-0">Add lab tests to build your catalog.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th class="d-none d-md-table-cell">Category</th>
                                <th class="d-none d-md-table-cell">Sample Type</th>
                                <th class="text-end">Price</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($labTests as $test)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $test->code }}</span></td>
                                    <td>
                                        <a href="{{ route('lab-tests.show', $test) }}" class="fw-semibold text-decoration-none">
                                            {{ $test->name }}
                                        </a>
                                        @if ($test->unit)
                                            <div class="small text-muted">Unit: {{ $test->unit }}</div>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $test->category ?? '-' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $test->sample_type ?? '-' }}</td>
                                    <td class="text-end">${{ number_format($test->price, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $test->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $test->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('lab-tests.show', $test) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('lab_test.edit')
                                                <a href="{{ route('lab-tests.edit', $test) }}" class="btn btn-sm btn-outline-warning">
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
            @endif
        </div>
    </div>

    @if ($labTests->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $labTests->withQueryString()->links() }}
        </div>
    @endif
</x-auth-layout>
