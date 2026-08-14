<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">New Appointment</h4>
        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
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

    <form method="POST" action="{{ route('appointments.store') }}">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Appointment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" class="form-select" id="patient-select" required>
                                    <option value="">Select Patient</option>
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}"
                                            {{ old('patient_id', $selectedPatient) == $patient->id ? 'selected' : '' }}
                                            data-name="{{ $patient->name }}" data-email="{{ $patient->email }}"
                                            data-phone="{{ $patient->phone }}">
                                            {{ $patient->patient_number }} - {{ $patient->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select" id="department-select" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-select" id="doctor-select" required>
                                    <option value="">Select Doctor</option>
                                    @foreach ($doctors as $doc)
                                        <option value="{{ $doc->id }}"
                                            {{ old('doctor_id', $selectedDoctor) == $doc->id ? 'selected' : '' }}
                                            data-days="{{ json_encode($doc->available_days) }}"
                                            data-start="{{ $doc->start_time }}" data-end="{{ $doc->end_time }}"
                                            data-fee="{{ $doc->consultation_fee }}">
                                            {{ $doc->name }} {{ $doc->department ? '(' . $doc->department->name . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" id="date-input"
                                    value="{{ old('date') }}" min="{{ now()->format('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Time <span class="text-danger">*</span></label>
                                <input type="time" name="time" class="form-control" id="time-input"
                                    value="{{ old('time') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                <select name="duration" class="form-select" required>
                                    @foreach ([15, 30, 45, 60, 90, 120] as $d)
                                        <option value="{{ $d }}" {{ old('duration', 30) == $d ? 'selected' : '' }}>
                                            {{ $d }} minutes
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="message" class="form-control" rows="3"
                                    placeholder="Reason for visit or special instructions">{{ old('message') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Doctor Info</h6>
                    </div>
                    <div class="card-body" id="doctor-info">
                        <p class="text-muted mb-0">Select a doctor to see availability.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Schedule Appointment
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const doctorSelect = document.getElementById('doctor-select');
            const dateInput = document.getElementById('date-input');
            const doctorInfo = document.getElementById('doctor-info');

            function updateDoctorInfo() {
                const selected = doctorSelect.options[doctorSelect.selectedIndex];
                if (!selected || !selected.value) {
                    doctorInfo.innerHTML = '<p class="text-muted mb-0">Select a doctor to see availability.</p>';
                    return;
                }

                const days = JSON.parse(selected.dataset.days || '[]');
                const start = selected.dataset.start || '';
                const end = selected.dataset.end || '';
                const fee = selected.dataset.fee || '0';
                const dayNames = {1:'Mon',2:'Tue',3:'Wed',4:'Thu',5:'Fri',6:'Sat',7:'Sun'};
                const hoursValid = start && end && start.substring(0,5) < end.substring(0,5);

                doctorInfo.innerHTML = `
                    <div class="mb-2"><strong>Available Days:</strong><br>
                        ${days.map(d => dayNames[d]).join(', ') || 'None set'}
                    </div>
                    <div class="mb-2"><strong>Hours:</strong> ${hoursValid ? start.substring(0,5) + ' - ' + end.substring(0,5) : '<span class="text-danger">Schedule not set up yet</span>'}</div>
                    <div><strong>Fee:</strong> $${parseFloat(fee).toFixed(2)}</div>
                `;
            }

            doctorSelect.addEventListener('change', updateDoctorInfo);
            updateDoctorInfo();
        });
    </script>
    @endpush
</x-auth-layout>
