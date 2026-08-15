<x-auth-layout>
    <x-page-header title="Walk-in Patient" subtitle="Add a walk-in patient to the queue"
        :breadcrumbs="[['label' => 'Queue', 'url' => route('queue.index')], ['label' => 'Walk-in']]">
        <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Queue
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

    <form method="POST" action="{{ route('queue.walkin') }}" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>Patient Information</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Select Patient <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" id="patient-select" required>
                                <option value="">Select Patient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->patient_number }} - {{ $patient->name }}
                                    </option>
                                @endforeach
                            </select>
                            <a href="{{ route('patients.create') }}" class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-plus-lg"></i> New
                            </a>
                            @error('patient_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Doctor Selection</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Doctor <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" id="doctor-select" required>
                            <option value="">Select Doctor</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}
                                    data-days="{{ json_encode($doc->available_days) }}"
                                    data-start="{{ $doc->start_time }}" data-end="{{ $doc->end_time }}">
                                    {{ $doc->name }} {{ $doc->department ? '(' . $doc->department->name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="doctor-info" class="mt-2">
                            <p class="text-muted small mb-0">Select a doctor to see availability.</p>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Reason for visit or notes">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add to Queue
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const doctorSelect = document.getElementById('doctor-select');
            const doctorInfo = document.getElementById('doctor-info');

            function updateDoctorInfo() {
                const selected = doctorSelect.options[doctorSelect.selectedIndex];
                if (!selected || !selected.value) {
                    doctorInfo.innerHTML = '<p class="text-muted small mb-0">Select a doctor to see availability.</p>';
                    return;
                }

                const days = JSON.parse(selected.dataset.days || '[]');
                const start = selected.dataset.start || '';
                const end = selected.dataset.end || '';
                const dayNames = {1:'Mon',2:'Tue',3:'Wed',4:'Thu',5:'Fri',6:'Sat',7:'Sun'};
                const hoursValid = start && end && start.substring(0, 5) < end.substring(0, 5);

                doctorInfo.innerHTML = `
                    <div class="mb-1">
                        <strong class="small text-muted d-block">Available Days</strong>
                        <div class="small">${days.length ? days.map(d => dayNames[d]).join(', ') : '<span class="text-danger">No days set</span>'}</div>
                    </div>
                    <div class="mb-0">
                        <strong class="small text-muted d-block">Hours</strong>
                        <div class="small">${hoursValid ? `${start.substring(0,5)} - ${end.substring(0,5)}` : '<span class="text-danger">Schedule not set up yet</span>'}</div>
                    </div>
                `;
            }

            doctorSelect.addEventListener('change', updateDoctorInfo);
            updateDoctorInfo();
        });
    </script>
    @endpush
</x-auth-layout>
