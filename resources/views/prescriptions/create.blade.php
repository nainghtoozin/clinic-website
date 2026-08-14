<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">New Prescription</h4>
            @if ($consultation)
                <small class="text-muted">For consultation #{{ $consultation->id }}</small>
            @endif
        </div>
        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('prescriptions.store') }}" id="prescriptionForm">
        @csrf

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
                                <input type="text" class="form-control" value="{{ $patient?->name }} ({{ $patient?->patient_number }})" disabled>
                                <input type="hidden" name="patient_id" value="{{ $patient?->id }}">
                                <div class="form-text text-muted">Linked from consultation #{{ $consultation->id }}</div>
                                @error('patient_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="{{ $doctor?->name }}" disabled>
                                <input type="hidden" name="doctor_id" value="{{ $doctor?->id }}">
                                <div class="form-text">Prescriptions are written by the consultation's doctor.</div>
                                @error('doctor_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prescribed Date <span class="text-danger">*</span></label>
                                <input type="date" name="prescribed_date" class="form-control @error('prescribed_date') is-invalid @enderror" value="{{ old('prescribed_date', now()->toDateString()) }}" required>
                                @error('prescribed_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}" placeholder="General prescription notes">
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
                        <div class="item-row mb-3 p-3 border rounded" data-index="0">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Medicine <span class="text-danger">*</span></label>
                                    <select name="items[0][medicine_id]" class="form-select @error('items.0.medicine_id') is-invalid @enderror" required>
                                        <option value="">Select medicine</option>
                                        @foreach ($medicines as $medicine)
                                            <option value="{{ $medicine->id }}" data-price="{{ $medicine->unit_price }}">
                                                {{ $medicine->name }} ({{ $medicine->strength ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.0.medicine_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Dosage <span class="text-danger">*</span></label>
                                    <input type="text" name="items[0][dosage]" class="form-control @error('items.0.dosage') is-invalid @enderror" value="{{ old('items.0.dosage') }}" placeholder="1 tablet" required>
                                    @error('items.0.dosage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Frequency <span class="text-danger">*</span></label>
                                    <input type="text" name="items[0][frequency]" class="form-control @error('items.0.frequency') is-invalid @enderror" value="{{ old('items.0.frequency') }}" placeholder="3 times daily" required>
                                    @error('items.0.frequency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Duration</label>
                                    <input type="text" name="items[0][duration]" class="form-control @error('items.0.duration') is-invalid @enderror" value="{{ old('items.0.duration') }}" placeholder="7 days">
                                    @error('items.0.duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Qty <span class="text-danger">*</span></label>
                                    <input type="number" name="items[0][quantity]" class="form-control @error('items.0.quantity') is-invalid @enderror" value="{{ old('items.0.quantity', '1') }}" min="1" required>
                                    @error('items.0.quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Instructions</label>
                                    <input type="text" name="items[0][instructions]" class="form-control" value="{{ old('items.0.instructions') }}" placeholder="After meals">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Prescription Summary</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Add medications to the prescription. Each medication needs a dosage, frequency, and quantity.</p>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-1"></i> Create Prescription
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-auth-layout>

<script>
    let itemIndex = 1;

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