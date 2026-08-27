<x-auth-layout>
    <x-page-header title="Communication History" subtitle="{{ $patient->name }}"
        :breadcrumbs="[['label' => 'Patients', 'url' => route('patients.index')], ['label' => $patient->name, 'url' => route('patients.show', $patient)], ['label' => 'Communications']]">
        <a href="{{ route('patients.medical-record', $patient) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Medical Record
        </a>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;font-size:1.8rem;">
                        {{ strtoupper(substr($patient->name, 0, 1)) }}
                    </div>
                    <h5 class="mb-0">{{ $patient->name }}</h5>
                    <small class="text-muted">{{ $patient->patient_number }}</small>
                    <div class="mt-3">
                        <small class="text-muted d-block"><i class="bi bi-phone me-1"></i> {{ $patient->phone ?? '-' }}</small>
                        <small class="text-muted d-block"><i class="bi bi-envelope me-1"></i> {{ $patient->email ?? '-' }}</small>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('patients.medical-record', $patient) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-folder me-1"></i> Medical Record
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted d-flex align-items-center">
                    <i class="bi bi-chat-dots me-1"></i>{{ $communications->total() }} communication(s)
                </small>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    @if ($communications->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted">No Communications</h6>
                            <p class="small text-muted mb-0">No communication records for this patient yet.</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($communications as $comm)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <span class="badge {{ $comm->getContactMethodBadgeClass() }} me-1">{{ $comm->contact_method_label }}</span>
                                            <span class="badge {{ $comm->getPurposeBadgeClass() }} me-1">{{ $comm->purpose_label }}</span>
                                            <span class="badge {{ $comm->getOutcomeBadgeClass() }}">{{ $comm->outcome_label }}</span>
                                        </div>
                                        <small class="text-muted">{{ $comm->contacted_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    @if ($comm->appointment)
                                        <div class="small mb-1">
                                            <i class="bi bi-calendar me-1"></i>
                                            <a href="{{ route('appointments.show', $comm->appointment) }}">
                                                {{ $comm->appointment->appointment_number }}
                                            </a>
                                            - {{ fmt_date($comm->appointment->date) }} {{ $comm->appointment->time }}
                                        </div>
                                    @endif
                                    @if ($comm->note)
                                        <div class="small text-muted">{{ $comm->note }}</div>
                                    @endif
                                    @if ($comm->follow_up_date && !$comm->follow_up_completed)
                                        <div class="small mt-1">
                                            <i class="bi bi-bell text-warning me-1"></i>
                                            Follow-up: {{ $comm->follow_up_date->format('d M Y') }}
                                            @if ($comm->isFollowUpOverdue())
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            @endif
                                            @if ($comm->follow_up_note)
                                                <br><span class="text-muted">{{ $comm->follow_up_note }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="mt-1">
                                        <small class="text-muted">By {{ $comm->user->name ?? '-' }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($communications->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $communications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>
