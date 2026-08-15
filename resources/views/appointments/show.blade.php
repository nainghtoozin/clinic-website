<x-auth-layout>
    <x-page-header title="Appointment Details" subtitle="{{ $appointment->appointment_number }}"
        :breadcrumbs="[['label' => 'Appointments', 'url' => route('appointments.index')], ['label' => $appointment->appointment_number]]">
        @if ($appointment->isScheduled())
            @can('appointment.edit')
                <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                    <i class="bi bi-pencil me-1"></i> Reschedule
                </a>
            @endcan
        @endif
        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-page-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Appointment Info</h6>
                    <span class="badge {{ $appointment->status->badgeClass() }}">
                        {{ $appointment->status->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Appointment #</label>
                            <div class="fw-semibold">{{ $appointment->appointment_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <div>
                                <span class="badge {{ $appointment->status->badgeClass() }}">
                                    {{ $appointment->status->label() }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date</label>
                            <div class="fw-semibold">{{ fmt_date($appointment->date) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Time</label>
                            <div class="fw-semibold">{{ $appointment->time ? fmt_time($appointment->time) : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Duration</label>
                            <div>{{ $appointment->duration ? $appointment->duration . ' minutes' : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Notes</label>
                            <div>{{ $appointment->message ?? '-' }}</div>
                        </div>
                        @if ($appointment->isCancelled() && $appointment->cancel_reason)
                            <div class="col-12">
                                <label class="form-label text-muted small">Cancellation Reason</label>
                                <div class="alert alert-danger py-2 mb-0">{{ $appointment->cancel_reason }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Patient</h6>
                </div>
                <div class="card-body">
                    @if ($appointment->patient)
                        <div class="mb-2">
                            <label class="form-label text-muted small">Patient #</label>
                            <div>{{ $appointment->patient->patient_number }}</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-muted small">Name</label>
                            <div class="fw-semibold">{{ $appointment->patient->name }}</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-muted small">Phone</label>
                            <div>{{ $appointment->patient->phone ?? '-' }}</div>
                        </div>
                        <div>
                            <label class="form-label text-muted small">Email</label>
                            <div>{{ $appointment->patient->email ?? '-' }}</div>
                        </div>
                    @else
                        <div class="text-muted">Legacy record - no patient linked.</div>
                        <div class="mt-2">
                            <strong>{{ $appointment->name }}</strong><br>
                            {{ $appointment->email }}<br>
                            {{ $appointment->phone }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Doctor</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label text-muted small">Name</label>
                        <div class="fw-semibold">{{ $appointment->doctor->name }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small">Department</label>
                        <div>{{ $appointment->department->name ?? '-' }}</div>
                    </div>
                    @if ($appointment->doctor->consultation_fee)
                        <div>
                            <label class="form-label text-muted small">Consultation Fee</label>
                            <div>${{ number_format($appointment->doctor->consultation_fee, 2) }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @if ($appointment->isScheduled())
                        @can('appointment.edit')
                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-warning w-100">
                                <i class="bi bi-pencil me-1"></i> Reschedule
                            </a>
                        @endcan
                    @endif

                    @if (!$appointment->isCancelled() && !$appointment->isCompleted() && count($allowedTransitions) > 0)
                        @can('appointment.edit')
                            <button type="button" class="btn btn-primary w-100" x-data @click="window.dispatchEvent(new CustomEvent('open-status-modal'))">
                                <i class="bi bi-arrow-repeat me-1"></i> Change Status
                            </button>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Status Change Modal --}}
    <div
        x-data="{
            open: false,
            status: '',
            note: '',
            allowed: @js($allowedTransitions),
            currentLabel: @js($appointment->status->label()),
            noteRequired: @js(\App\Enums\AppointmentStatus::Cancelled->value),
            loading: false,
            error: '',
            terminal: false,
            openModal() {
                this.terminal = this.allowed.length === 0;
                this.status = this.allowed.length ? this.allowed[0].value : '';
                this.note = '';
                this.error = '';
                this.loading = false;
                this.open = true;
            },
            closeModal() {
                if (!this.loading) this.open = false;
            },
            isNoteRequired() {
                return this.status === this.noteRequired;
            },
            async submit(form) {
                if (this.loading || this.terminal) return;
                this.error = '';
                if (!this.status) {
                    this.error = 'Please choose a new status.';
                    return;
                }
                if (this.isNoteRequired() && !this.note.trim()) {
                    this.error = 'A cancellation / rejection reason is required.';
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form)
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok) {
                        this.open = false;
                        history.replaceState(null, '', window.location.pathname + window.location.search);
                        window.location.reload();
                    } else {
                        this.error = data.message || (data.errors && Object.values(data.errors).flat()[0]) || 'Something went wrong. Please try again.';
                    }
                } catch (e) {
                    this.error = 'Network error. Please check your connection and try again.';
                } finally {
                    this.loading = false;
                }
            }
        }"
        x-init="
            $watch('open', (val) => { if (val) document.body.classList.add('modal-open'); else document.body.classList.remove('modal-open'); });
            if (window.location.hash === '#status') openModal();
        "
        @open-status-modal.window="openModal()"
        @keydown.escape.window="closeModal()"
    >
        <div x-show="open" x-cloak class="modal appointment-status-modal" tabindex="-1" role="dialog" aria-modal="true"
        :aria-hidden="open ? 'false' : 'true'"
        @click.self="closeModal()">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('appointments.status', $appointment) }}" novalidate
                        @submit.prevent="submit($event.target)">
                        @csrf
                        <div class="modal-header">
                            <h6 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Change Status</h6>
                            <button type="button" class="btn-close" @click="closeModal()"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Current Status</label>
                                <input type="text" class="form-control" value="{{ $appointment->status->label() }} - {{ $appointment->appointment_number }}" disabled>
                            </div>
                            <div x-show="terminal" x-cloak class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-1"></i> No further status changes are available for this appointment.
                            </div>
                            <template x-if="!terminal">
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">New Status <span class="text-danger">*</span></label>
                                        <select name="status" x-model="status" class="form-select">
                                            <template x-for="option in allowed" :key="option.value">
                                                <option :value="option.value" x-text="option.label"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Note / Reason</label>
                                        <textarea name="note" x-model="note" class="form-control" rows="3"
                                            :required="isNoteRequired()"
                                            placeholder="Add a note or reason for this status change..."></textarea>
                                        <div x-show="isNoteRequired()" x-cloak class="form-text text-danger">
                                            A cancellation / rejection reason is required.
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="error" x-cloak class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i><span x-text="error"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" @click="closeModal()">Close</button>
                            <button type="submit" class="btn btn-primary" :disabled="loading || terminal">
                                <i class="bi bi-check-lg me-1" x-show="!loading"></i>
                                <span x-show="!loading">Update Status</span>
                                <span x-show="loading" x-cloak class="spinner-border spinner-border-sm"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div x-show="open" x-cloak class="modal-backdrop fade show"></div>
    </div>

    {{-- Status History --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Status History</h6>
        </div>
        <div class="card-body">
            @if ($appointment->statusHistories->isEmpty())
                <div class="text-center text-muted py-3">No status changes recorded yet.</div>
            @else
                <div class="position-relative ps-3" style="border-left: 2px solid #e9ecef;">
                    @foreach ($appointment->statusHistories->sortByDesc('created_at') as $history)
                        <div class="mb-3 ps-3 position-relative">
                            <span class="position-absolute" style="left: -7px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #0d6efd;"></span>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                <div class="fw-semibold">
                                    @if ($history->from_status)
                                        <span class="badge {{ $history->from_status->badgeClass() }}">{{ $history->from_label }}</span>
                                        <i class="bi bi-arrow-right mx-1"></i>
                                    @endif
                                    <span class="badge {{ $history->to_status->badgeClass() }}">{{ $history->to_status->label() }}</span>
                                </div>
                                <small class="text-muted">{{ fmt_datetime($history->created_at) }}</small>
                            </div>
                            @if ($history->note)
                                <div class="text-muted small mt-1"><i class="bi bi-chat-left-text me-1"></i>{{ $history->note }}</div>
                            @endif
                            <div class="text-muted small mt-1">
                                <i class="bi bi-person me-1"></i>{{ $history->changedBy?->name ?? 'System' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>

<style>
    [x-cloak] {
        display: none !important;
    }

    .appointment-status-modal {
        display: block;
    }
</style>
