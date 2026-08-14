<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Walk-in Patient</h4>
            <small class="text-muted">Add a walk-in patient to the queue</small>
        </div>
        <a href="{{ route('queue.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Queue
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('queue.walkin') }}">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>Patient Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Select Patient <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="patient_id" class="form-select" id="patient-select" required>
                                        <option value="">Select Patient</option>
                                        @foreach ($patients as $patient)
                                            <option value="{{ $patient->id }}"
                                                {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->patient_number }} - {{ $patient->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('patients.create') }}" class="btn btn-outline-primary" target="_blank">
                                        <i class="bi bi-plus-lg"></i> New
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Doctor Selection</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select" id="doctor-select" required>
                                <option value="">Select Doctor</option>
                                @foreach ($doctors as $doc)
                                    <option value="{{ $doc->id }}"
                                        {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->name }} {{ $doc->department ? '(' . $doc->department->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="doctor-info">
                            <p class="text-muted small mb-0">Select a doctor to see availability.</p>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
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

                if (days.length > 0) {
                    doctorInfo.innerHTML = `
                        <div class="mb-1"><strong class="small">Available:</strong>
                            ${days.map(d => dayNames[d]).join(', ')}
                        </div>
                        <div><strong class="small">Hours:</strong> ${start.substring(0,5)} - ${end.substring(0,5)}</div>
                    `;
                }
            }

            doctorSelect.addEventListener('change', updateDoctorInfo);
            updateDoctorInfo();
        });
    </script>
    @endpush
</x-auth-layout>
