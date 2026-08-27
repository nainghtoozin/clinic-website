<x-auth-layout>
    <x-page-header title="Invoices" subtitle="Patient billing and invoice management"
        :breadcrumbs="[['label' => 'Invoices']]">
        @can('invoice.create')
            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-circle me-1"></i> New Invoice
            </a>
        @endcan
    </x-page-header>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('invoices.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search invoice # or patient name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
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
            <i class="bi bi-receipt me-1"></i>{{ $invoices->total() }} invoice(s)
            @if (request()->has('search') && request('search'))
                &middot; matching &ldquo;{{ request('search') }}&rdquo;
            @endif
        </small>
        @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
            <a href="{{ route('invoices.index') }}" class="small text-decoration-none">
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
                        <th>Invoice #</th>
                        <th>Patient</th>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-end d-none d-lg-table-cell">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><span class="badge bg-primary">{{ $invoice->invoice_number }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-primary">{{ initials($invoice->patient?->name) }}</span>
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ $invoice->patient?->name ?? '-' }}</div>
                                        @if ($invoice->patient)
                                            <small class="text-muted">{{ $invoice->patient->patient_number }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ fmt_date($invoice->created_at) }}</td>
                            <td class="text-end fw-semibold">${{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end d-none d-lg-table-cell">${{ number_format($invoice->amount_paid, 2) }}</td>
                            <td class="text-end">
                                @if ($invoice->balance > 0)
                                    <span class="text-danger fw-semibold">${{ number_format($invoice->balance, 2) }}</span>
                                @else
                                    <span class="text-success">$0.00</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $invoice->getStatusBadgeClass() }}">
                                    <span class="status-dot"></span>{{ $invoice->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('invoice.edit')
                                        @if ($invoice->isDraft() || $invoice->isIssued() || $invoice->isPartiallyPaid())
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                    @endcan
                                    @can('payment.create')
                                        @if ($invoice->canReceivePayment())
                                            <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-outline-success" title="Record Payment">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        @endif
                                    @endcan
                                    @can('invoice.delete')
                                        @if ($invoice->isCancelled() && $invoice->payments()->count() === 0)
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteInvoiceModal"
                                                data-invoice-id="{{ $invoice->id }}"
                                                data-invoice-number="{{ $invoice->invoice_number }}"
                                                data-invoice-patient="{{ $invoice->patient?->name ?? '-' }}"
                                                data-invoice-date="{{ fmt_date($invoice->created_at) }}"
                                                data-invoice-total="${{ number_format($invoice->total, 2) }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No invoices found</div>
                                <small>Try adjusting your search or filters.</small>
                                @can('invoice.create')
                                    <div class="mt-3">
                                        <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Create Invoice
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
    @if ($invoices->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
            <small class="text-muted">
                Showing {{ $invoices->firstItem() }}&ndash;{{ $invoices->lastItem() }} of {{ $invoices->total() }}
            </small>
            <div>{{ $invoices->links() }}</div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @can('invoice.delete')
    <div class="modal fade" id="deleteInvoiceModal" tabindex="-1" aria-labelledby="deleteInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" id="deleteInvoiceForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0">
                        <h5 class="modal-title" id="deleteInvoiceModalLabel">
                            <i class="bi bi-exclamation-triangle text-danger me-2"></i>Delete Invoice
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-info-circle mt-1"></i>
                            <div>This action will soft-delete the invoice and reverse any associated inventory effects. This cannot be undone.</div>
                        </div>
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label text-muted small">Invoice #</label>
                                    <div class="fw-semibold" id="modal-invoice-number">-</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">Patient</label>
                                    <div class="fw-semibold" id="modal-invoice-patient">-</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">Date</label>
                                    <div id="modal-invoice-date">-</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">Total</label>
                                    <div class="fw-semibold" id="modal-invoice-total">-</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Deletion Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required
                                placeholder="Enter the reason for deleting this invoice..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Confirm Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    @push('scripts')
    <script>
        const deleteInvoiceModal = document.getElementById('deleteInvoiceModal');
        if (deleteInvoiceModal) {
            deleteInvoiceModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const invoiceId = button.getAttribute('data-invoice-id');
                const invoiceNumber = button.getAttribute('data-invoice-number');
                const invoicePatient = button.getAttribute('data-invoice-patient');
                const invoiceDate = button.getAttribute('data-invoice-date');
                const invoiceTotal = button.getAttribute('data-invoice-total');

                document.getElementById('deleteInvoiceForm').action = `/invoices/${invoiceId}`;
                document.getElementById('modal-invoice-number').textContent = invoiceNumber;
                document.getElementById('modal-invoice-patient').textContent = invoicePatient;
                document.getElementById('modal-invoice-date').textContent = invoiceDate;
                document.getElementById('modal-invoice-total').textContent = invoiceTotal;
            });
        }
    </script>
    @endpush
</x-auth-layout>
