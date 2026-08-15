<x-auth-layout>
    <x-page-header title="Record Payment" subtitle="{{ $invoice ? 'Invoice ' . $invoice->invoice_number : 'Select an invoice to pay' }}"
        :breadcrumbs="[['label' => 'Payments', 'url' => route('payments.index')], ['label' => 'Record Payment']]">
        <a href="{{ $invoice ? route('invoices.show', $invoice) : route('payments.index') }}"
            class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-2" role="alert">
            <i class="bi bi-exclamation-octagon fs-5 mt-1"></i>
            <div>
                <strong class="d-block mb-1">Please fix the following errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('payments.store') }}" id="paymentForm" novalidate>
                @csrf

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoice Selection</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Invoice <span class="text-danger">*</span></label>
                            <select name="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror" required id="invoiceSelect">
                                <option value="">Select invoice</option>
                                @foreach ($invoices as $inv)
                                    <option value="{{ $inv->id }}" data-balance="{{ $inv->balance }}" data-total="{{ $inv->total }}"
                                        data-patient="{{ $inv->patient?->name ?? '' }}"
                                        {{ old('invoice_id', $invoice?->id) == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} - {{ $inv->patient?->name ?? '' }} (Balance: ${{ number_format($inv->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('invoice_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info mb-0" id="selected-invoice-info">
                            @if ($invoice)
                                <div class="d-flex flex-wrap gap-3">
                                    <div><small class="text-muted d-block">Invoice</small><strong>{{ $invoice->invoice_number }}</strong></div>
                                    <div><small class="text-muted d-block">Patient</small><strong>{{ $invoice->patient?->name ?? '-' }}</strong></div>
                                    <div><small class="text-muted d-block">Total</small><strong>${{ number_format($invoice->total, 2) }}</strong></div>
                                    <div><small class="text-muted d-block">Paid</small><span class="text-success fw-semibold">${{ number_format($invoice->amount_paid, 2) }}</span></div>
                                    <div><small class="text-muted d-block">Balance Due</small><span class="text-danger fw-bold">${{ number_format($invoice->balance, 2) }}</span></div>
                                </div>
                            @else
                                <i class="bi bi-info-circle me-1"></i> Select an invoice to see its balance and remaining amount.
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-cash me-2"></i>Payment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount-input" class="form-control @error('amount') is-invalid @enderror"
                                    step="0.01" min="0.01" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text" id="amount-hint">Enter the amount to pay.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Select method</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mobile_payment" {{ old('payment_method') === 'mobile_payment' ? 'selected' : '' }}>Mobile Payment</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Paid At <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="paid_at" class="form-control @error('paid_at') is-invalid @enderror"
                                    value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('paid_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" placeholder="Receipt #, Transaction ID, etc.">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-1"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            @if ($invoice && $invoice->payments->isNotEmpty())
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Payment History</h6>
                    </div>
                    <div class="card-body p-0">
                        @foreach ($invoice->payments as $payment)
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <div class="min-w-0">
                                    <div class="fw-semibold">${{ number_format($payment->amount, 2) }}</div>
                                    <small class="text-muted">{{ $payment->getPaymentMethodLabel() }}</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">{{ fmt_datetime($payment->paid_at) }}</small>
                                    @can('payment.view')
                                        <a href="{{ route('payments.receipt', $payment) }}" class="small">Receipt</a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center text-muted py-4">
                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                        <small>Select an invoice to view its payment history.</small>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('invoiceSelect');
            const amountInput = document.getElementById('amount-input');
            const hint = document.getElementById('amount-hint');
            const info = document.getElementById('selected-invoice-info');

            function updateInvoice() {
                const opt = select.options[select.selectedIndex];
                if (!opt || !opt.value) {
                    info.innerHTML = '<i class="bi bi-info-circle me-1"></i> Select an invoice to see its balance and remaining amount.';
                    hint.textContent = 'Enter the amount to pay.';
                    amountInput.max = '';
                    return;
                }

                const balance = parseFloat(opt.dataset.balance) || 0;
                const total = parseFloat(opt.dataset.total) || 0;
                const patient = opt.dataset.patient || '';

                amountInput.max = Math.max(0, balance).toFixed(2);

                if (balance <= 0) {
                    hint.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>This invoice has no remaining balance.</span>';
                } else {
                    hint.innerHTML = 'Enter the amount to pay. Maximum: <strong>$' + balance.toFixed(2) + '</strong>';
                }

                info.innerHTML = `
                    <div class="d-flex flex-wrap gap-3">
                        <div><small class="text-muted d-block">Invoice</small><strong>${opt.text.split(' - ')[0]}</strong></div>
                        <div><small class="text-muted d-block">Patient</small><strong>${patient}</strong></div>
                        <div><small class="text-muted d-block">Total</small><strong>$${total.toFixed(2)}</strong></div>
                        <div><small class="text-muted d-block">Balance Due</small><span class="text-danger fw-bold">$${balance.toFixed(2)}</span></div>
                    </div>`;
            }

            select.addEventListener('change', updateInvoice);
            updateInvoice();
        });
    </script>
    @endpush
</x-auth-layout>
