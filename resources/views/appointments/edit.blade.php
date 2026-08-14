<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Reschedule Appointment</h4>
            <small class="text-muted">{{ $appointment->appointment_number }}</small>
        </div>
        <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
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

    <form method="POST" action="{{ route('appointments.update', $appointment) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Appointment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select Patient</option>
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}"
                                            {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->patient_number }} - {{ $patient->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ old('department_id', $appointment->department_id) == $dept->id ? 'selected' : '' }}>
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
                                            {{ old('doctor_id', $appointment->doctor_id) == $doc->id ? 'selected' : '' }}
                                            data-days="{{ json_encode($doc->available_days) }}"
                                            data-start="{{ $doc->start_time }}" data-end="{{ $doc->end_time }}">
                                            {{ $doc->name }} {{ $doc->department ? '(' . $doc->department->name . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ old('date', $appointment->date->format('Y-m-d')) }}"
                                    min="{{ now()->format('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Time <span class="text-danger">*</span></label>
                                <input type="time" name="time" class="form-control"
                                    value="{{ old('time', $appointment->time) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                <select name="duration" class="form-select" required>
                                    @foreach ([15, 30, 45, 60, 90, 120] as $d)
                                        <option value="{{ $d }}" {{ old('duration', $appointment->duration) == $d ? 'selected' : '' }}>
                                            {{ $d }} minutes
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="message" class="form-control" rows="3">{{ old('message', $appointment->message) }}</textarea>
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
            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Reschedule
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
                    doctorInfo.innerHTML = '<p class="text-muted mb-0">Select a doctor to see availability.</p>';
                    return;
                }

                const days = JSON.parse(selected.dataset.days || '[]');
                const start = selected.dataset.start || '';
                const end = selected.dataset.end || '';
                const dayNames = {1:'Mon',2:'Tue',3:'Wed',4:'Thu',5:'Fri',6:'Sat',7:'Sun'};

                doctorInfo.innerHTML = `
                    <div class="mb-2"><strong>Available Days:</strong><br>
                        ${days.map(d => dayNames[d]).join(', ')}
                    </div>
                    <div><strong>Hours:</strong> ${start.substring(0,5)} - ${end.substring(0,5)}</div>
                `;
            }

            doctorSelect.addEventListener('change', updateDoctorInfo);
            updateDoctorInfo();
        });
    </script>
    @endpush
</x-auth-layout>
