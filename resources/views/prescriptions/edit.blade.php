<x-auth-layout>
    <x-page-header title="Edit Prescription" subtitle="{{ $prescription->patient?->name ?? '' }} &middot; {{ $prescription->prescription_number }}"
        :breadcrumbs="[['label' => 'Prescriptions', 'url' => route('prescriptions.index')], ['label' => $prescription->prescription_number]]">
        <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
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

    <form method="POST" action="{{ route('prescriptions.update', $prescription) }}" id="prescriptionForm" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Prescription details --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-file-medical me-2"></i>Prescription Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Patient</label>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-primary">{{ initials($prescription->patient?->name) }}</span>
                                    <div class="fw-semibold">{{ $prescription->patient?->name ?? '-' }}</div>
                                </div>
                                <input type="hidden" name="patient_id" value="{{ $prescription->patient_id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Doctor</label>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($prescription->doctor?->profile_image)
                                        <img src="{{ Storage::url($prescription->doctor->profile_image) }}" class="avatar" alt="">
                                    @else
                                        <span class="avatar bg-info">{{ initials($prescription->doctor?->name) }}</span>
                                    @endif
                                    <div class="fw-semibold">{{ $prescription->doctor?->name ?? '-' }}</div>
                                </div>
                                <input type="hidden" name="doctor_id" value="{{ $prescription->doctor_id }}">
                                @if ($prescription->consultation_id)
                                    <input type="hidden" name="consultation_id" value="{{ $prescription->consultation_id }}">
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prescribed Date <span class="text-danger">*</span></label>
                                <input type="date" name="prescribed_date" class="form-control @error('prescribed_date') is-invalid @enderror"
                                    value="{{ old('prescribed_date', $prescription->prescribed_date->toDateString()) }}" required>
                                @error('prescribed_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                                    value="{{ old('notes', $prescription->notes) }}" placeholder="General prescription notes">
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Medication items --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-capsule me-2"></i>Medication Items</h6>
                        @if ($medicines->count())
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                                <i class="bi bi-plus-circle me-1"></i> Add Item
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($medicines->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-capsule fs-1 d-block mb-2"></i>
                                <div class="fw-medium text-dark">No active medicines available</div>
                            </div>
                        @else
                            <div id="itemsContainer">
                                @foreach ($prescription->items as $index => $item)
                                    <div class="item-row mb-3 p-3 border rounded" data-index="{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small text-muted fw-semibold"><i class="bi bi-list-ol me-1"></i>Medication</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                                <i class="bi bi-trash me-1"></i> Remove
                                            </button>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Medicine <span class="text-danger">*</span></label>
                                                <select class="form-select item-medicine" name="items[{{ $index }}][medicine_id]" required>
                                                    <option value="">Select medicine</option>
                                                    @foreach ($medicines as $medicine)
                                                        <option value="{{ $medicine->id }}"
                                                            {{ old("items.{$index}.medicine_id", $item->medicine_id) == $medicine->id ? 'selected' : '' }}>
                                                            {{ $medicine->name }} {{ $medicine->strength ? '(' . $medicine->strength . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Dosage <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control item-dosage" name="items[{{ $index }}][dosage]"
                                                    value="{{ old("items.{$index}.dosage", $item->dosage) }}" placeholder="e.g., 1 tablet" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Frequency <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control item-frequency" name="items[{{ $index }}][frequency]"
                                                    value="{{ old("items.{$index}.frequency", $item->frequency) }}" placeholder="e.g., 3 times daily" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control item-quantity" name="items[{{ $index }}][quantity]"
                                                    value="{{ old("items.{$index}.quantity", $item->quantity) }}" min="1" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Duration</label>
                                                <input type="text" class="form-control item-duration" name="items[{{ $index }}][duration]"
                                                    value="{{ old("items.{$index}.duration", $item->duration) }}" placeholder="e.g., 7 days">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Instructions</label>
                                                <input type="text" class="form-control item-instructions" name="items[{{ $index }}][instructions]"
                                                    value="{{ old("items.{$index}.instructions", $item->instructions) }}" placeholder="e.g., After meals">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <select id="medicine-source" class="d-none">
                                @foreach ($medicines as $medicine)
                                    <option value="{{ $medicine->id }}">{{ $medicine->name }} {{ $medicine->strength ? '(' . $medicine->strength . ')' : '' }}</option>
                                @endforeach
                            </select>
                            <template id="itemTemplate">
                                <div class="item-row mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-muted fw-semibold"><i class="bi bi-list-ol me-1"></i>Medication</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                            <i class="bi bi-trash me-1"></i> Remove
                                        </button>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Medicine <span class="text-danger">*</span></label>
                                            <select class="form-select item-medicine" name="items[0][medicine_id]" required>
                                                <option value="">Select medicine</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Dosage <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control item-dosage" name="items[0][dosage]" placeholder="e.g., 1 tablet" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Frequency <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control item-frequency" name="items[0][frequency]" placeholder="e.g., 3 times daily" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control item-quantity" name="items[0][quantity]" value="1" min="1" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Duration</label>
                                            <input type="text" class="form-control item-duration" name="items[0][duration]" placeholder="e.g., 7 days">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Instructions</label>
                                            <input type="text" class="form-control item-instructions" name="items[0][instructions]" placeholder="e.g., After meals">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Prescription Summary</h6>
                    </div>
                    <div class="card-body">
                        @if ($medicines->count())
                            <p class="text-muted small mb-3">Update the medication items as needed.</p>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-1"></i> Update Prescription
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        let itemIndex = {{ $prescription->items->count() }};

        function fillMedicineSelect(select) {
            const source = document.getElementById('medicine-source');
            if (!source) return;
            select.innerHTML = '<option value="">Select medicine</option>';
            Array.from(source.options).forEach(function (option) {
                select.appendChild(option.cloneNode(true));
            });
        }

        function addItem() {
            const container = document.getElementById('itemsContainer');
            if (!container) return;
            const template = document.getElementById('itemTemplate');
            const div = template.content.cloneNode(true);
            const row = div.querySelector('.item-row');
            row.dataset.index = itemIndex;
            row.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace('[0]', '[' + itemIndex + ']');
            });
            fillMedicineSelect(row.querySelector('.item-medicine'));
            container.appendChild(div);
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
    @endpush
</x-auth-layout>
