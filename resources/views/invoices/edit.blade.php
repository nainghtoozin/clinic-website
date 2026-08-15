<x-auth-layout>
    <x-page-header title="Edit Invoice {{ $invoice->invoice_number }}" subtitle="{{ $invoice->patient?->name ?? '' }}"
        :breadcrumbs="[['label' => 'Invoices', 'url' => route('invoices.index')], ['label' => $invoice->invoice_number], ['label' => 'Edit']]">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
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

    <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoiceForm" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Patient & Doctor (read-only) --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient & Doctor</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-6">
                                <label class="form-label text-muted small mb-0">Patient</label>
                                <div class="fw-semibold">{{ $invoice->patient?->name ?? '-' }}</div>
                                <small class="text-muted">{{ $invoice->patient?->patient_number ?? '' }}</small>
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="form-label text-muted small mb-0">Doctor</label>
                                <div>{{ $invoice->doctor?->name ?? '-' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small mb-0">Amount Paid</label>
                                <div class="text-success fw-semibold">${{ number_format($invoice->amount_paid, 2) }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small mb-0">Balance</label>
                                <div class="fw-semibold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">${{ number_format($invoice->balance, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Invoice Items --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Invoice Items</h6>
                        <div class="d-flex gap-2">
                            @if ($medicines->count())
                                <select id="quick-medicine" class="form-select form-select-sm" style="max-width:220px;">
                                    <option value="">Add medicine...</option>
                                    @foreach ($medicines as $medicine)
                                        <option value="{{ $medicine->id }}" data-name="{{ $medicine->name . ($medicine->strength ? ' (' . $medicine->strength . ')' : '') }}"
                                            data-price="{{ $medicine->unit_price }}">
                                            {{ $medicine->name }} {{ $medicine->strength ? '(' . $medicine->strength . ')' : '' }} - ${{ number_format($medicine->unit_price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @if ($services->count())
                                <select id="quick-service" class="form-select form-select-sm" style="max-width:220px;">
                                    <option value="">Add service...</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" data-name="{{ $service->title }}"
                                            data-price="{{ $service->price }}">
                                            {{ $service->title }} - ${{ number_format($service->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                                <i class="bi bi-plus-circle me-1"></i> Add Line
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            @foreach ($invoice->items as $index => $item)
                                <div class="item-row mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-muted fw-semibold"><i class="bi bi-list-ol me-1"></i>Line Item</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                            <i class="bi bi-trash me-1"></i> Remove
                                        </button>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Description <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control item-description" name="items[{{ $index }}][description]"
                                                value="{{ old("items.{$index}.description", $item->description) }}" required>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label">Type <span class="text-danger">*</span></label>
                                            <select class="form-select item-type" name="items[{{ $index }}][type]" required>
                                                <option value="consultation" {{ old("items.{$index}.type", $item->type) === 'consultation' ? 'selected' : '' }}>Consultation</option>
                                                <option value="medicine" {{ old("items.{$index}.type", $item->type) === 'medicine' ? 'selected' : '' }}>Medicine</option>
                                                <option value="service" {{ old("items.{$index}.type", $item->type) === 'service' ? 'selected' : '' }}>Service</option>
                                                <option value="other" {{ old("items.{$index}.type", $item->type) === 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control item-qty" name="items[{{ $index }}][quantity]"
                                                value="{{ old("items.{$index}.quantity", $item->quantity) }}" min="1" required>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control item-price" name="items[{{ $index }}][unit_price]"
                                                step="0.01" min="0" value="{{ old("items.{$index}.unit_price", $item->unit_price) }}" required>
                                        </div>
                                        <div class="col-6 col-md-3 d-flex align-items-end justify-content-end">
                                            <div class="fw-semibold item-line-total">${{ number_format($item->total, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <template id="itemTemplate">
                            <div class="item-row mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-muted fw-semibold"><i class="bi bi-list-ol me-1"></i>Line Item</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                        <i class="bi bi-trash me-1"></i> Remove
                                    </button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Description <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control item-description" name="items[0][description]" placeholder="Description of charge" required>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select class="form-select item-type" name="items[0][type]" required>
                                            <option value="consultation">Consultation</option>
                                            <option value="medicine">Medicine</option>
                                            <option value="service">Service</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control item-qty" name="items[0][quantity]" value="1" min="1" required>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control item-price" name="items[0][unit_price]" step="0.01" min="0" value="0.00" required>
                                    </div>
                                    <div class="col-6 col-md-3 d-flex align-items-end justify-content-end">
                                        <div class="fw-semibold item-line-total">$0.00</div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Summary --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Discount</label>
                            <input type="number" name="discount" id="discount-input" class="form-control @error('discount') is-invalid @enderror" step="0.01" min="0" value="{{ old('discount', $invoice->discount) }}">
                            @error('discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tax</label>
                            <input type="number" name="tax" id="tax-input" class="form-control @error('tax') is-invalid @enderror" step="0.01" min="0" value="{{ old('tax', $invoice->tax) }}">
                            @error('tax')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span id="preview-subtotal" class="fw-semibold">${{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount</span>
                            <span id="preview-discount" class="text-success">-${{ number_format($invoice->discount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax</span>
                            <span id="preview-tax" class="text-danger">+${{ number_format($invoice->tax, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total</span>
                            <span id="preview-total" class="fw-bold fs-5">${{ number_format($invoice->total, 2) }}</span>
                        </div>
                        <small class="text-muted d-block mb-3"><i class="bi bi-info-circle me-1"></i>Final totals are recalculated by the server on save.</small>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-1"></i> Update Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        let itemIndex = {{ $invoice->items->count() }};

        function addItem() {
            const container = document.getElementById('itemsContainer');
            if (!container) return;
            const template = document.getElementById('itemTemplate');
            const div = template.content.cloneNode(true);
            div.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace('[0]', '[' + itemIndex + ']');
            });
            container.appendChild(div);
            itemIndex++;
            recalc();
        }

        function removeItem(btn) {
            if (document.querySelectorAll('.item-row').length > 1) {
                btn.closest('.item-row').remove();
                recalc();
            } else {
                alert('At least one line item is required.');
            }
        }

        function recalc() {
            let subtotal = 0;
            document.querySelectorAll('.item-row').forEach(function (row) {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const lineTotal = qty * price;
                subtotal += lineTotal;
                row.querySelector('.item-line-total').textContent = '$' + lineTotal.toFixed(2);
            });
            const discount = parseFloat(document.getElementById('discount-input').value) || 0;
            const tax = parseFloat(document.getElementById('tax-input').value) || 0;
            const total = Math.max(0, subtotal - discount + tax);

            document.getElementById('preview-subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('preview-discount').textContent = '-$' + discount.toFixed(2);
            document.getElementById('preview-tax').textContent = '+$' + tax.toFixed(2);
            document.getElementById('preview-total').textContent = '$' + total.toFixed(2);
        }

        document.getElementById('itemsContainer').addEventListener('input', recalc);
        document.getElementById('discount-input').addEventListener('input', recalc);
        document.getElementById('tax-input').addEventListener('input', recalc);

        document.getElementById('quick-medicine')?.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt.value) return;
            addItem();
            const rows = document.querySelectorAll('.item-row');
            const row = rows[rows.length - 1];
            row.querySelector('.item-description').value = opt.dataset.name;
            row.querySelector('.item-type').value = 'medicine';
            row.querySelector('.item-price').value = opt.dataset.price;
            recalc();
            this.value = '';
        });

        document.getElementById('quick-service')?.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt.value) return;
            addItem();
            const rows = document.querySelectorAll('.item-row');
            const row = rows[rows.length - 1];
            row.querySelector('.item-description').value = opt.dataset.name;
            row.querySelector('.item-type').value = 'service';
            row.querySelector('.item-price').value = opt.dataset.price;
            recalc();
            this.value = '';
        });

        document.addEventListener('DOMContentLoaded', recalc);
    </script>
    @endpush
</x-auth-layout>
