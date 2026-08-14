<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Create Invoice</h4>
            @if ($consultation)<small class="text-muted">For consultation #{{ $consultation->id }}</small>@endif
        </div>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient & Doctor</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                    <option value="">Select patient</option>
                                    @foreach ($patients as $p)
                                        <option value="{{ $p->id }}" {{ old('patient_id', $patient?->id) == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->patient_number }})</option>
                                    @endforeach
                                </select>
                                @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Doctor</label>
                                <select name="doctor_id" class="form-select">
                                    <option value="">Select doctor</option>
                                    @foreach ($doctors as $d)
                                        <option value="{{ $d->id }}" {{ old('doctor_id', $doctor?->id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($consultation)
                                <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                                <input type="hidden" name="appointment_id" value="{{ $consultation->appointment_id }}">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Invoice Items</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="bi bi-plus-circle me-1"></i> Add</button>
                    </div>
                    <div class="card-body" id="itemsContainer">
                        <div class="item-row mb-3 p-3 border rounded">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Description <span class="text-danger">*</span></label><input type="text" name="items[0][description]" class="form-control" required></div>
                                <div class="col-md-2"><label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="items[0][type]" class="form-select" required><option value="consultation">Consultation</option><option value="medicine">Medicine</option><option value="service">Service</option><option value="other">Other</option></select>
                                </div>
                                <div class="col-md-2"><label class="form-label">Qty <span class="text-danger">*</span></label><input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required></div>
                                <div class="col-md-2"><label class="form-label">Price <span class="text-danger">*</span></label><input type="number" name="items[0][unit_price]" class="form-control" step="0.01" min="0" value="0.00" required></div>
                                <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Summary</h6></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Discount</label><input type="number" name="discount" class="form-control" step="0.01" min="0" value="{{ old('discount', '0.00') }}"></div>
                        <div class="mb-3"><label class="form-label">Tax</label><input type="number" name="tax" class="form-control" step="0.01" min="0" value="{{ old('tax', '0.00') }}"></div>
                        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
                        <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-1"></i> Create Invoice</button></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-auth-layout>

<script>
let itemIndex = 1;
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
