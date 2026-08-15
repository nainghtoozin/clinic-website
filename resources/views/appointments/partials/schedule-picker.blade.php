@php
    $preselectedTime = old('time', isset($appointment) && $appointment->time ? substr($appointment->time, 0, 5) : null);
    $slotsUrl = route('appointments.availability');
@endphp

{{-- Doctor availability info --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Doctor Availability</h6>
    </div>
    <div class="card-body" id="doctor-info">
        <p class="text-muted mb-0">Select a department, doctor and date to see availability.</p>
    </div>
</div>

{{-- Available time slots --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Available Times</h6>
    </div>
    <div class="card-body">
        <div id="slots-wrap">
            <p class="text-muted mb-0">Select a doctor and date to see available times.</p>
        </div>
    </div>
</div>

<input type="hidden" id="preselected-time" value="{{ $preselectedTime }}">

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deptSelect = document.getElementById('department-select');
        const doctorSelect = document.getElementById('doctor-select');
        const dateInput = document.getElementById('date-input');
        const durationSelect = document.getElementById('duration-select');
        const doctorInfo = document.getElementById('doctor-info');
        const slotsWrap = document.getElementById('slots-wrap');
        const preselectedTime = document.getElementById('preselected-time').value;
        const slotsUrl = @js($slotsUrl);

        const dayNames = { 1: 'Mon', 2: 'Tue', 3: 'Wed', 4: 'Thu', 5: 'Fri', 6: 'Sat', 7: 'Sun' };

        function filterDoctorsByDepartment() {
            const deptId = deptSelect.value;
            const selectedValue = doctorSelect.value;

            Array.from(doctorSelect.options).forEach(function (option) {
                if (!option.value) return;
                option.hidden = deptId !== '' && option.dataset.department !== deptId;
            });

            if (selectedValue) {
                const selectedOption = Array.from(doctorSelect.options).find(o => o.value === selectedValue);
                if (selectedOption && !selectedOption.hidden) {
                    return;
                }
            }

            doctorSelect.value = '';
            doctorSelect.dispatchEvent(new Event('change'));
        }

        function renderDoctorInfo() {
            const selected = doctorSelect.options[doctorSelect.selectedIndex];
            if (!selected || !selected.value) {
                doctorInfo.innerHTML = '<p class="text-muted mb-0">Select a department, doctor and date to see availability.</p>';
                return;
            }

            const days = JSON.parse(selected.dataset.days || '[]');
            const start = selected.dataset.start || '';
            const end = selected.dataset.end || '';
            const fee = selected.dataset.fee || '';
            const hoursValid = start && end && start.substring(0, 5) < end.substring(0, 5);

            doctorInfo.innerHTML = `
                <div class="mb-2">
                    <label class="form-label text-muted small mb-0">Available Days</label>
                    <div class="d-flex flex-wrap gap-1">
                        ${days.length
                            ? days.map(d => `<span class="badge bg-primary-subtle text-primary">${dayNames[d]}</span>`).join('')
                            : '<span class="text-muted">No days set</span>'}
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label text-muted small mb-0">Working Hours</label>
                    <div>${hoursValid ? `${start.substring(0, 5)} &ndash; ${end.substring(0, 5)}` : '<span class="text-danger">Schedule not set up yet</span>'}</div>
                </div>
                ${fee !== '' && fee !== null ? `
                <div>
                    <label class="form-label text-muted small mb-0">Consultation Fee</label>
                    <div>$${parseFloat(fee).toFixed(2)}</div>
                </div>` : ''}
            `;
        }

        function loadSlots() {
            const doctorId = doctorSelect.value;
            const date = dateInput.value;
            const duration = durationSelect ? durationSelect.value : 30;

            if (!doctorId || !date) {
                slotsWrap.innerHTML = '<p class="text-muted mb-0">Select a doctor and date to see available times.</p>';
                return;
            }

            slotsWrap.innerHTML = '<p class="text-muted mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Loading available times...</p>';

            fetch(`${slotsUrl}?doctor_id=${encodeURIComponent(doctorId)}&date=${encodeURIComponent(date)}&duration=${encodeURIComponent(duration)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(data => {
                    const slots = [...data.slots];

                    // On edit, keep the current appointment time selectable even though it is booked.
                    if (preselectedTime && !slots.includes(preselectedTime)) {
                        slots.unshift(preselectedTime);
                    }

                    if (slots.length === 0) {
                        slotsWrap.innerHTML = `<div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>${data.message || 'No available times for this doctor on this date.'}
                        </div>`;
                        return;
                    }

                    const chips = slots.map(slot => {
                        const checked = (slot === preselectedTime) ? ' checked' : '';
                        const label = slot === preselectedTime ? `${slot} (current)` : slot;
                        return `<label class="slot-chip btn btn-outline-primary ${slot === preselectedTime ? 'active' : ''}">
                            <input type="radio" name="time" value="${slot}" class="btn-check slot-radio"${checked} required>
                            ${label}
                        </label>`;
                    }).join('');

                    slotsWrap.innerHTML = `<div class="d-flex flex-wrap gap-2">${chips}</div>
                        <small class="text-muted d-block mt-2">Slots refresh with the selected date, doctor and duration.</small>`;

                    slotsWrap.querySelectorAll('.slot-radio').forEach(function (radio) {
                        radio.addEventListener('change', function () {
                            slotsWrap.querySelectorAll('.slot-chip').forEach(chip => chip.classList.remove('active'));
                            this.closest('.slot-chip').classList.add('active');
                        });
                    });
                })
                .catch(() => {
                    slotsWrap.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Could not load available times. Please try again.</div>';
                });
        }

        if (deptSelect) deptSelect.addEventListener('change', filterDoctorsByDepartment);
        if (doctorSelect) doctorSelect.addEventListener('change', function () {
            renderDoctorInfo();
            loadSlots();
        });
        if (dateInput) dateInput.addEventListener('change', loadSlots);
        if (durationSelect) durationSelect.addEventListener('change', loadSlots);

        filterDoctorsByDepartment();
        renderDoctorInfo();
        loadSlots();
    });
</script>
@endpush
