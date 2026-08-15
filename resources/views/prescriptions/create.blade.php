<x-auth-layout>
    <x-page-header title="Add Prescription" subtitle="Prescribe medications for a consultation"
        :breadcrumbs="[['label' => 'Prescriptions', 'url' => route('prescriptions.index')], ['label' => 'Add Prescription']]">
        <a href="{{ $consultation ? route('consultations.show', $consultation) : route('prescriptions.index') }}"
            class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    {{-- Workflow --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2 small align-items-center">
                <span class="fw-semibold text-muted"><i class="bi bi-diagram-3 me-1"></i>Workflow:</span>
                <span class="badge bg-primary">Consultation</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge bg-primary">Add Prescription</span>
            </div>
        </div>
    </div>

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

    <form method="POST" action="{{ route('prescriptions.store') }}" id="prescriptionForm" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Prescription details (from consultation) --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-file-medical me-2"></i>Prescription Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Patient</label>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar bg-primary">{{ initials($patient?->name) }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ $patient?->name }}</div>
                                        <small class="text-muted">{{ $patient?->patient_number }}</small>
                                    </div>
                                </div>
                                <input type="hidden" name="patient_id" value="{{ $patient?->id }}">
                                <div class="form-text text-muted">For consultation #{{ $consultation->id }} - patient is locked to the consultation.</div>
                                @error('patient_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Doctor</label>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($doctor?->profile_image)
                                        <img src="{{ Storage::url($doctor->profile_image) }}" class="avatar" alt="">
                                    @else
                                        <span class="avatar bg-info">{{ initials($doctor?->name) }}</span>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $doctor?->name }}</div>
                                        <small class="text-muted">{{ $doctor?->department?->name ?? '' }}</small>
                                    </div>
                                </div>
                                <input type="hidden" name="doctor_id" value="{{ $doctor?->id }}">
                                <div class="form-text text-muted">Prescriptions are written by the consultation's doctor.</div>
                                @error('doctor_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prescribed Date <span class="text-danger">*</span></label>
                                <input type="date" name="prescribed_date" class="form-control @error('prescribed_date') is-invalid @enderror"
                                    value="{{ old('prescribed_date', now()->toDateString()) }}" required>
                                @error('prescribed_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                                    value="{{ old('notes') }}" placeholder="General prescription notes">
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
                                <small>Add medicines to the formulary before prescribing.</small>
                                @can('medicine.create')
                                    <div class="mt-3">
                                        <a href="{{ route('medicines.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Add Medicine
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        @else
                            <div id="itemsContainer"></div>
                            <select id="medicine-source" class="d-none">
                                @foreach ($medicines as $medicine)
                                    <option value="{{ $medicine->id }}">{{ $medicine->name }} {{ $medicine->strength ? '(' . $medicine->strength . ')' : '' }} {{ $medicine->stock_quantity > 0 ? '' : '- OUT OF STOCK' }}</option>
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
                            <p class="text-muted small mb-3">Add medications to the prescription. Each medication needs a dosage, frequency, and quantity.</p>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-1"></i> Create Prescription
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
        let itemIndex = 0;

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
                el.id = el.name.replace(/[\[\]]/g, '_');
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

        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('itemsContainer');
            if (container && container.children.length === 0) {
                addItem();
            }
        });
    </script>
    @endpush
</x-auth-layout>
