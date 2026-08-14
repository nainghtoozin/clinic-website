<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-0">Record Payment</h4></div>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoice Selection</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Invoice <span class="text-danger">*</span></label>
                            <select name="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror" required id="invoiceSelect">
                                <option value="">Select invoice</option>
                                @foreach ($invoices as $inv)
                                    <option value="{{ $inv->id }}" data-balance="{{ $inv->balance }}" data-total="{{ $inv->total }}" {{ old('invoice_id', $invoice?->id) == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} - {{ $inv->patient->name ?? '' }} (Balance: ${{ number_format($inv->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('invoice_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @if ($invoice)
                            <div class="alert alert-info mb-0">
                                <strong>{{ $invoice->invoice_number }}</strong> | Total: ${{ number_format($invoice->total, 2) }} | Paid: ${{ number_format($invoice->amount_paid, 2) }} | <strong>Balance: ${{ number_format($invoice->balance, 2) }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-cash me-2"></i>Payment Details</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" step="0.01" min="0.01" value="{{ old('amount') }}" required>
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Paid At <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="paid_at" class="form-control @error('paid_at') is-invalid @enderror" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('paid_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check-circle me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</x-auth-layout>
