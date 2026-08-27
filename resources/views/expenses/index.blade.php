<x-auth-layout>
    <x-page-header title="Expenses" subtitle="Track and manage clinic expenses"
        :breadcrumbs="[['label' => 'Expenses']]">
        <div class="d-flex gap-2">
            @can('expense_category.view')
                <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-tags me-1"></i> Categories
                </a>
            @endcan
            @can('expense.create')
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Expense
                </button>
            @endcan
        </div>
    </x-page-header>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('expenses.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Number, description, vendor..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Payment</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="">All Methods</option>
                            @foreach (\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                                <option value="{{ $key }}" {{ request('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label small text-muted mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label small text-muted mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto col-md-1 d-flex gap-2">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i></button>
                        @if (request()->hasAny(['search', 'category_id', 'payment_method', 'date_from', 'date_to']))
                            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted"><i class="bi bi-receipt me-1"></i>{{ $expenses->total() }} expense(s)</small>
        @php
            $totalAmount = \App\Models\Expense::active()
                ->when(request('category_id'), fn($q) => $q->where('expense_category_id', request('category_id')))
                ->when(request('payment_method'), fn($q) => $q->where('payment_method', request('payment_method')))
                ->when(request('date_from'), fn($q) => $q->where('expense_date', '>=', request('date_from')))
                ->when(request('date_to'), fn($q) => $q->where('expense_date', '<=', request('date_to')))
                ->sum('amount');
        @endphp
        <small class="text-muted fw-semibold">Total: {{ number_format($totalAmount, 2) }}</small>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($expenses->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-receipt fs-1 text-muted d-block mb-2"></i>
                    <h6 class="text-muted">No Expenses Found</h6>
                    <p class="small text-muted mb-0">Record your first expense to get started.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Category</th>
                                <th class="d-none d-md-table-cell">Description</th>
                                <th class="text-end">Amount</th>
                                <th class="d-none d-md-table-cell">Method</th>
                                <th class="d-none d-md-table-cell">Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $expense)
                                <tr>
                                    <td class="small">{{ $expense->expense_date->format('d M Y') }}</td>
                                    <td class="small fw-semibold">{{ $expense->expense_number }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $expense->expenseCategory->name ?? '-' }}</span></td>
                                    <td class="d-none d-md-table-cell small text-muted">{{ Str::limit($expense->description, 50) }}</td>
                                    <td class="text-end small fw-semibold">{{ number_format($expense->amount, 2) }}</td>
                                    <td class="d-none d-md-table-cell"><span class="badge {{ $expense->getPaymentMethodBadgeClass() }}">{{ $expense->payment_method_label }}</span></td>
                                    <td class="d-none d-md-table-cell"><span class="badge {{ $expense->getStatusBadgeClass() }}">{{ $expense->status_label }}</span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewExpenseModal{{ $expense->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if ($expense->isActive())
                                                @can('expense.edit')
                                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editExpenseModal{{ $expense->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                @endcan
                                                @can('expense.delete')
                                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="d-inline" onsubmit="return confirm('Cancel this expense?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel"><i class="bi bi-x-circle"></i></button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- View Modal --}}
                                <div class="modal fade" id="viewExpenseModal{{ $expense->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title">{{ $expense->expense_number }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">Date</dt>
                                                    <dd class="col-sm-8">{{ $expense->expense_date->format('d M Y') }}</dd>
                                                    <dt class="col-sm-4">Category</dt>
                                                    <dd class="col-sm-8">{{ $expense->expenseCategory->name ?? '-' }}</dd>
                                                    <dt class="col-sm-4">Amount</dt>
                                                    <dd class="col-sm-8 fw-bold">{{ number_format($expense->amount, 2) }}</dd>
                                                    <dt class="col-sm-4">Payment</dt>
                                                    <dd class="col-sm-8"><span class="badge {{ $expense->getPaymentMethodBadgeClass() }}">{{ $expense->payment_method_label }}</span></dd>
                                                    <dt class="col-sm-4">Vendor</dt>
                                                    <dd class="col-sm-8">{{ $expense->vendor ?? '-' }}</dd>
                                                    <dt class="col-sm-4">Description</dt>
                                                    <dd class="col-sm-8">{{ $expense->description }}</dd>
                                                    @if ($expense->note)
                                                        <dt class="col-sm-4">Note</dt>
                                                        <dd class="col-sm-8">{{ $expense->note }}</dd>
                                                    @endif
                                                    @if ($expense->reference_number)
                                                        <dt class="col-sm-4">Reference</dt>
                                                        <dd class="col-sm-8">{{ $expense->reference_number }}</dd>
                                                    @endif
                                                    <dt class="col-sm-4">Status</dt>
                                                    <dd class="col-sm-8"><span class="badge {{ $expense->getStatusBadgeClass() }}">{{ $expense->status_label }}</span></dd>
                                                    <dt class="col-sm-4">Created By</dt>
                                                    <dd class="col-sm-8">{{ $expense->createdBy->name ?? '-' }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Modal --}}
                                @can('expense.edit')
                                    @if ($expense->isActive())
                                        <div class="modal fade" id="editExpenseModal{{ $expense->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('expenses.update', $expense) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-header">
                                                            <h6 class="modal-title">Edit {{ $expense->expense_number }}</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                                                    <select name="expense_category_id" class="form-select form-select-sm" required>
                                                                        @foreach ($categories as $cat)
                                                                            <option value="{{ $cat->id }}" {{ $expense->expense_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                                                    <input type="number" name="amount" class="form-control form-control-sm" value="{{ $expense->amount }}" step="0.01" min="0.01" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                                                    <select name="payment_method" class="form-select form-select-sm" required>
                                                                        @foreach (\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                                                                            <option value="{{ $key }}" {{ $expense->payment_method === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                                                    <input type="date" name="expense_date" class="form-control form-control-sm" value="{{ $expense->expense_date->format('Y-m-d') }}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Vendor</label>
                                                                    <input type="text" name="vendor" class="form-control form-control-sm" value="{{ $expense->vendor }}" maxlength="200">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Reference #</label>
                                                                    <input type="text" name="reference_number" class="form-control form-control-sm" value="{{ $expense->reference_number }}" maxlength="100">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                                                    <input type="text" name="description" class="form-control form-control-sm" value="{{ $expense->description }}" required maxlength="500">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Note</label>
                                                                    <textarea name="note" class="form-control form-control-sm" rows="2" maxlength="2000">{{ $expense->note }}</textarea>
                                                                </div>
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
                                    @endif
                                @endcan
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($expenses->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $expenses->withQueryString()->links() }}
        </div>
    @endif

    @can('expense.create')
        <div class="modal fade" id="addExpenseModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('expenses.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h6 class="modal-title">Record Expense</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="expense_category_id" class="form-select form-select-sm" required>
                                        <option value="">Select...</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" class="form-control form-control-sm" step="0.01" min="0.01" value="{{ old('amount') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-select form-select-sm" required>
                                        @foreach (\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                                            <option value="{{ $key }}" {{ old('payment_method', 'cash') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expense_date" class="form-control form-control-sm" value="{{ old('expense_date', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Vendor</label>
                                    <input type="text" name="vendor" class="form-control form-control-sm" value="{{ old('vendor') }}" maxlength="200" placeholder="Supplier or payee">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Reference #</label>
                                    <input type="text" name="reference_number" class="form-control form-control-sm" value="{{ old('reference_number') }}" maxlength="100">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <input type="text" name="description" class="form-control form-control-sm" value="{{ old('description') }}" required maxlength="500" placeholder="What was this expense for?">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Note</label>
                                    <textarea name="note" class="form-control form-control-sm" rows="2" maxlength="2000" placeholder="Additional details...">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</x-auth-layout>
