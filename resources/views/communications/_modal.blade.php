<div class="modal fade" id="addCommunicationModal" tabindex="-1" aria-labelledby="addCommunicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('communications.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCommunicationModalLabel">Log Communication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="comm_patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="comm_patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">Select patient...</option>
                                @foreach (\App\Models\Patient::orderBy('name')->get() as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }} ({{ $patient->patient_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="comm_appointment_id" class="form-label">Related Appointment</label>
                            <select name="appointment_id" id="comm_appointment_id" class="form-select @error('appointment_id') is-invalid @enderror">
                                <option value="">None</option>
                            </select>
                            <small class="text-muted">Appointments will load after selecting a patient</small>
                            @error('appointment_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="comm_contact_method" class="form-label">Contact Method <span class="text-danger">*</span></label>
                            <select name="contact_method" id="comm_contact_method" class="form-select @error('contact_method') is-invalid @enderror" required>
                                <option value="">Select method...</option>
                                @foreach (\App\Models\Communication::CONTACT_METHODS as $key => $label)
                                    <option value="{{ $key }}" {{ old('contact_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('contact_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="comm_purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose" id="comm_purpose" class="form-select @error('purpose') is-invalid @enderror" required>
                                <option value="">Select purpose...</option>
                                @foreach (\App\Models\Communication::PURPOSES as $key => $label)
                                    <option value="{{ $key }}" {{ old('purpose') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="comm_outcome" class="form-label">Outcome <span class="text-danger">*</span></label>
                            <select name="outcome" id="comm_outcome" class="form-select @error('outcome') is-invalid @enderror" required>
                                <option value="">Select outcome...</option>
                                @foreach (\App\Models\Communication::OUTCOMES as $key => $label)
                                    <option value="{{ $key }}" {{ old('outcome') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('outcome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="comm_contacted_at" class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="contacted_at" id="comm_contacted_at" class="form-control @error('contacted_at') is-invalid @enderror"
                                value="{{ old('contacted_at', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('contacted_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="comm_note" class="form-label">Note</label>
                            <textarea name="note" id="comm_note" class="form-control @error('note') is-invalid @enderror" rows="3" maxlength="2000">{{ old('note') }}</textarea>
                            @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6 class="text-muted mb-3"><i class="bi bi-bell me-1"></i> Follow-up</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="comm_follow_up_date" class="form-label">Follow-up Date</label>
                            <input type="date" name="follow_up_date" id="comm_follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror"
                                value="{{ old('follow_up_date') }}" min="{{ now()->toDateString() }}">
                            @error('follow_up_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="comm_follow_up_note" class="form-label">Follow-up Note</label>
                            <textarea name="follow_up_note" id="comm_follow_up_note" class="form-control @error('follow_up_note') is-invalid @enderror" rows="2" maxlength="1000">{{ old('follow_up_note') }}</textarea>
                            @error('follow_up_note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Communication</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('comm_patient_id')?.addEventListener('change', function() {
    const select = document.getElementById('comm_appointment_id');
    select.innerHTML = '<option value="">Loading...</option>';
    if (!this.value) {
        select.innerHTML = '<option value="">None</option>';
        return;
    }
    fetch(`/patients/${this.value}/appointments-json`)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">None</option>';
            data.forEach(a => {
                select.innerHTML += `<option value="${a.id}">${a.appointment_number} - ${a.date} ${a.time}</option>`;
            });
        })
        .catch(() => { select.innerHTML = '<option value="">None</option>'; });
});
</script>
@endpush
