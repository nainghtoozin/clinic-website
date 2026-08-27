<x-auth-layout>
    <x-page-header title="Communication Details" subtitle="#{{ $communication->id }}"
        :breadcrumbs="[['label' => 'Communications', 'url' => route('communications.index')], ['label' => 'Details']]">
        <a href="{{ route('communications.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Communication Record</h6>
                        <div class="d-flex gap-2">
                            <span class="badge {{ $communication->getContactMethodBadgeClass() }}">{{ $communication->contact_method_label }}</span>
                            <span class="badge {{ $communication->getPurposeBadgeClass() }}">{{ $communication->purpose_label }}</span>
                            <span class="badge {{ $communication->getOutcomeBadgeClass() }}">{{ $communication->outcome_label }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Contacted At</dt>
                        <dd class="col-sm-9">{{ $communication->contacted_at->format('d M Y, H:i') }}</dd>

                        <dt class="col-sm-3">Patient</dt>
                        <dd class="col-sm-9">
                            <a href="{{ route('patients.show', $communication->patient) }}">{{ $communication->patient->name }}</a>
                            <span class="text-muted">({{ $communication->patient->patient_number }})</span>
                        </dd>

                        @if ($communication->appointment)
                            <dt class="col-sm-3">Appointment</dt>
                            <dd class="col-sm-9">
                                <a href="{{ route('appointments.show', $communication->appointment) }}">{{ $communication->appointment->appointment_number }}</a>
                                <span class="text-muted">({{ fmt_date($communication->appointment->date) }} {{ $communication->appointment->time }})</span>
                            </dd>
                        @endif

                        <dt class="col-sm-3">Recorded By</dt>
                        <dd class="col-sm-9">{{ $communication->user->name ?? '-' }}</dd>

                        @if ($communication->note)
                            <dt class="col-sm-3">Note</dt>
                            <dd class="col-sm-9">{{ $communication->note }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if ($communication->follow_up_date || $communication->follow_up_note)
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-bell me-1"></i> Follow-up</h6>
                            @if (!$communication->follow_up_completed)
                                <form method="POST" action="{{ route('communications.complete-follow-up', $communication) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-check-circle me-1"></i> Mark Complete
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Completed</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Follow-up Date</dt>
                            <dd class="col-sm-9">
                                {{ $communication->follow_up_date?->format('d M Y') ?? '-' }}
                                @if ($communication->isFollowUpOverdue())
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @elseif ($communication->isFollowUpDueToday())
                                    <span class="badge bg-warning text-dark ms-2">Due Today</span>
                                @endif
                            </dd>

                            @if ($communication->follow_up_note)
                                <dt class="col-sm-3">Follow-up Note</dt>
                                <dd class="col-sm-9">{{ $communication->follow_up_note }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Patient</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;font-size:1.5rem;">
                            {{ strtoupper(substr($communication->patient->name ?? 'P', 0, 1)) }}
                        </div>
                        <h6 class="mt-2 mb-0">{{ $communication->patient->name ?? '-' }}</h6>
                        <small class="text-muted">{{ $communication->patient->patient_number ?? '' }}</small>
                    </div>
                    <dl class="row small mb-0">
                        <dt class="col-5">Phone</dt>
                        <dd class="col-7">{{ $communication->patient->phone ?? '-' }}</dd>
                        <dt class="col-5">Email</dt>
                        <dd class="col-7">{{ $communication->patient->email ?? '-' }}</dd>
                    </dl>
                    <div class="mt-3">
                        <a href="{{ route('patients.medical-record', $communication->patient) }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-folder me-1"></i> Medical Record
                        </a>
                        <a href="{{ route('communications.patient', $communication->patient) }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                            <i class="bi bi-chat-dots me-1"></i> Communication History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
