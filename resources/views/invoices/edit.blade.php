<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-0">Edit Invoice {{ $invoice->invoice_number }}</h4></div>
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient & Doctor</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Patient</label><div class="fw-semibold">{{ $invoice->patient->name ?? '-' }}</div></div>
                            <div class="col-md-6"><label class="form-label">Doctor</label><div>{{ $invoice->doctor->name ?? '-' }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Invoice Items</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="bi bi-plus-circle me-1"></i> Add</button>
                    </div>
                    <div class="card-body" id="itemsContainer">
                        @foreach ($invoice->items as $index => $item)
                            <div class="item-row mb-3 p-3 border rounded">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Description <span class="text-danger">*</span></label><input type="text" name="items[{{ $index }}][description]" class="form-control" value="{{ $item->description }}" required></div>
                                    <div class="col-md-2"><label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="items[{{ $index }}][type]" class="form-select" required>
                                            <option value="consultation" {{ $item->type === 'consultation' ? 'selected' : '' }}>Consultation</option>
                                            <option value="medicine" {{ $item->type === 'medicine' ? 'selected' : '' }}>Medicine</option>
                                            <option value="service" {{ $item->type === 'service' ? 'selected' : '' }}>Service</option>
                                            <option value="other" {{ $item->type === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2"><label class="form-label">Qty <span class="text-danger">*</span></label><input type="number" name="items[{{ $index }}][quantity]" class="form-control" value="{{ $item->quantity }}" min="1" required></div>
                                    <div class="col-md-2"><label class="form-label">Price <span class="text-danger">*</span></label><input type="number" name="items[{{ $index }}][unit_price]" class="form-control" step="0.01" min="0" value="{{ $item->unit_price }}" required></div>
                                    <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Summary</h6></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Discount</label><input type="number" name="discount" class="form-control" step="0.01" min="0" value="{{ old('discount', $invoice->discount) }}"></div>
                        <div class="mb-3"><label class="form-label">Tax</label><input type="number" name="tax" class="form-control" step="0.01" min="0" value="{{ old('tax', $invoice->tax) }}"></div>
                        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $invoice->notes) }}</textarea></div>
                        <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-1"></i> Update Invoice</button></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-auth-layout>

<script>
let itemIndex = {{ $invoice->items->count() }};
function addItem() {
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend',
        `<div class="item-row mb-3 p-3 border rounded">
            <div class="row g-3">
                <div class="col-md-4"><input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="Description" required></div>
                <div class="col-md-2"><select name="items[${itemIndex}][type]" class="form-select" required><option value="consultation">Consultation</option><option value="medicine">Medicine</option><option value="service">Service</option><option value="other">Other</option></select></div>
                <div class="col-md-2"><input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" required></div>
                <div class="col-md-2"><input type="number" name="items[${itemIndex}][unit_price]" class="form-control" step="0.01" min="0" value="0.00" required></div>
                <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button></div>
            </div>
        </div>`
    );
    itemIndex++;
}
function removeItem(btn) {
    if (document.querySelectorAll('.item-row').length > 1) btn.closest('.item-row').remove();
}
</script>
