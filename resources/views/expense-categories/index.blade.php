<x-auth-layout>
    <x-page-header title="Expense Categories" subtitle="Manage expense categories"
        :breadcrumbs="[['label' => 'Expenses', 'url' => route('expenses.index')], ['label' => 'Categories']]">
        @can('expense_category.create')
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-circle me-1"></i> Add Category
            </button>
        @endcan
    </x-page-header>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($categories->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-tags fs-1 text-muted d-block mb-2"></i>
                    <h6 class="text-muted">No Categories</h6>
                    <p class="small text-muted mb-0">Create expense categories to organize your expenses.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="d-none d-md-table-cell">Description</th>
                                <th class="text-end">Total Expenses</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $cat)
                                <tr>
                                    <td class="fw-semibold small">{{ $cat->name }}</td>
                                    <td class="d-none d-md-table-cell small text-muted">{{ $cat->description ?: '-' }}</td>
                                    <td class="text-end small">{{ number_format($cat->expenses_sum_amount ?? 0, 2) }}</td>
                                    <td class="text-center small">{{ $cat->expenses_count }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @can('expense_category.edit')
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $cat->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('expense_category.delete')
                                            @if ($cat->expenses_count === 0)
                                                <form method="POST" action="{{ route('expense-categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>

                                @can('expense_category.edit')
                                    <div class="modal fade" id="editCategoryModal{{ $cat->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('expense-categories.update', $cat) }}">
                                                    @csrf @method('PUT')
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Edit Category</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required maxlength="100">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="form-control" rows="2" maxlength="500">{{ $cat->description }}</textarea>
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input type="hidden" name="is_active" value="0">
                                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $cat->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label">Active</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($categories->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $categories->links() }}
        </div>
    @endif

    @can('expense_category.create')
        <div class="modal fade" id="addCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('expense-categories.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h6 class="modal-title">Add Expense Category</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required maxlength="100" placeholder="e.g. Medical Supplies">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" maxlength="500" placeholder="Optional description"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</x-auth-layout>
