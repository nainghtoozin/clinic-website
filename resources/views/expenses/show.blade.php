<x-auth-layout>
    <x-page-header title="Expense Detail" subtitle="{{ $expense->expense_number }}"
        :breadcrumbs="[['label' => 'Expenses', 'url' => route('expenses.index')], ['label' => $expense->expense_number]]">
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Expense Information</h6>
                        <span class="badge {{ $expense->getStatusBadgeClass() }}">{{ $expense->status_label }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Reference</dt>
                        <dd class="col-sm-9 fw-semibold">{{ $expense->expense_number }}</dd>
                        <dt class="col-sm-3">Date</dt>
                        <dd class="col-sm-9">{{ $expense->expense_date->format('d M Y') }}</dd>
                        <dt class="col-sm-3">Category</dt>
                        <dd class="col-sm-9">{{ $expense->expenseCategory->name ?? '-' }}</dd>
                        <dt class="col-sm-3">Amount</dt>
                        <dd class="col-sm-9 fw-bold fs-5">{{ number_format($expense->amount, 2) }}</dd>
                        <dt class="col-sm-3">Payment Method</dt>
                        <dd class="col-sm-9"><span class="badge {{ $expense->getPaymentMethodBadgeClass() }}">{{ $expense->payment_method_label }}</span></dd>
                        <dt class="col-sm-3">Vendor</dt>
                        <dd class="col-sm-9">{{ $expense->vendor ?? '-' }}</dd>
                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $expense->description }}</dd>
                        @if ($expense->note)
                            <dt class="col-sm-3">Note</dt>
                            <dd class="col-sm-9">{{ $expense->note }}</dd>
                        @endif
                        @if ($expense->reference_number)
                            <dt class="col-sm-3">Reference #</dt>
                            <dd class="col-sm-9">{{ $expense->reference_number }}</dd>
                        @endif
                        <dt class="col-sm-3">Created By</dt>
                        <dd class="col-sm-9">{{ $expense->createdBy->name ?? '-' }}</dd>
                        <dt class="col-sm-3">Created At</dt>
                        <dd class="col-sm-9">{{ $expense->created_at->format('d M Y, H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Quick Info</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                            <i class="bi bi-receipt fs-3 text-danger"></i>
                        </div>
                        <h5 class="mt-2 mb-0 text-danger">{{ number_format($expense->amount, 2) }}</h5>
                        <small class="text-muted">{{ $expense->expense_date->format('d M Y') }}</small>
                    </div>
                    <div class="d-grid gap-2">
                        @if ($expense->isActive())
                            @can('expense.edit')
                                <a href="{{ route('expenses.index') }}" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-pencil me-1"></i> Edit from List
                                </a>
                            @endcan
                            @can('expense.delete')
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Cancel this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="bi bi-x-circle me-1"></i> Cancel Expense
                                    </button>
                                </form>
                            @endcan
                        @else
                            <div class="text-center text-muted small">This expense has been cancelled.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
