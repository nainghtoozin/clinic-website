<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Edit Prescription</h4>
            <small class="text-muted">{{ $prescription->prescription_number }}</small>
        </div>
        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('prescriptions.update', $prescription) }}" id="prescriptionForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-file-medical me-2"></i>Prescription Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Patient <span class="text-danger">*</span></label>
                                <input type="hidden" name="patient_id" value="{{ $prescription->patient_id }}">
                                <div class="fw-semibold">{{ $prescription->patient->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                <input type="hidden" name="doctor_id" value="{{ $prescription->doctor_id }}">
                                <div>{{ $prescription->doctor->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prescribed Date <span class="text-danger">*</span></label>
                                <input type="date" name="prescribed_date" class="form-control @error('prescribed_date') is-invalid @enderror" value="{{ old('prescribed_date', $prescription->prescribed_date->toDateString()) }}" required>
                                @error('prescribed_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes', $prescription->notes) }}" placeholder="General prescription notes">
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-capsule me-2"></i>Medication Items</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                            <i class="bi bi-plus-circle me-1"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body" id="itemsContainer">
                        @foreach ($prescription->items as $index => $item)
                            <div class="item-row mb-3 p-3 border rounded" data-index="{{ $index }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Medicine <span class="text-danger">*</span></label>
                                        <select name="items[{{ $index }}][medicine_id]" class="form-select" required>
                                            <option value="">Select medicine</option>
                                            @foreach ($medicines as $medicine)
                                                <option value="{{ $medicine->id }}" {{ old("items.{$index}.medicine_id", $item->medicine_id) == $medicine->id ? 'selected' : '' }}>
                                                    {{ $medicine->name }} ({{ $medicine->strength ?? '' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Dosage <span class="text-danger">*</span></label>
                                        <input type="text" name="items[{{ $index }}][dosage]" class="form-control" value="{{ old("items.{$index}.dosage", $item->dosage) }}" placeholder="1 tablet" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                                        <input type="text" name="items[{{ $index }}][frequency]" class="form-control" value="{{ old("items.{$index}.frequency", $item->frequency) }}" placeholder="3 times daily" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Duration</label>
                                        <input type="text" name="items[{{ $index }}][duration]" class="form-control" value="{{ old("items.{$index}.duration", $item->duration) }}" placeholder="7 days">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Qty <span class="text-danger">*</span></label>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control" value="{{ old("items.{$index}.quantity", $item->quantity) }}" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Instructions</label>
                                        <input type="text" name="items[{{ $index }}][instructions]" class="form-control" value="{{ old("items.{$index}.instructions", $item->instructions) }}" placeholder="After meals">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                        <i class="bi bi-trash me-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Prescription Summary</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Update the medication items as needed.</p>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-1"></i> Update Prescription
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-auth-layout>

<script>
    let itemIndex = {{ $prescription->items->count() }};

    function addItem() {
        const container = document.getElementById('itemsContainer');
        const template = `
            <div class="item-row mb-3 p-3 border rounded" data-index="${itemIndex}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Medicine <span class="text-danger">*</span></label>
                        <select name="items[${itemIndex}][medicine_id]" class="form-select" required>
                            <option value="">Select medicine</option>
                            @foreach ($medicines as $medicine)
                                <option value="{{ $medicine->id }}">{{ $medicine->name }} ({{ $medicine->strength ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Dosage <span class="text-danger">*</span></label>
                        <input type="text" name="items[${itemIndex}][dosage]" class="form-control" placeholder="1 tablet" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                        <input type="text" name="items[${itemIndex}][frequency]" class="form-control" placeholder="3 times daily" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Duration</label>
                        <input type="text" name="items[${itemIndex}][duration]" class="form-control" placeholder="7 days">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Qty <span class="text-danger">*</span></label>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Instructions</label>
                        <input type="text" name="items[${itemIndex}][instructions]" class="form-control" placeholder="After meals">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                        <i class="bi bi-trash me-1"></i> Remove
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        itemIndex++;
    }

    function removeItem(btn) {
        const row = btn.closest('.item-row');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
        } else {
            alert('At least one medication is required.');
        }
    }
</script>