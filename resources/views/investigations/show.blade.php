<x-auth-layout>
    <x-page-header title="Investigation Details" subtitle="{{ $investigation->labTest->name ?? '' }}"
        :breadcrumbs="[['label' => 'Investigations', 'url' => route('investigations.index')], ['label' => 'Details']]">
        @if ($investigation->canTransitionTo('in_progress') && auth()->user()->can('investigation.edit'))
            <form method="POST" action="{{ route('investigations.status', $investigation) }}" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="in_progress">
                <button type="submit" class="btn btn-warning btn-sm d-inline-flex align-items-center">
                    <i class="bi bi-play-circle me-1"></i> Start
                </button>
            </form>
        @endif
        @if ($investigation->canTransitionTo('completed') && auth()->user()->can('investigation.edit'))
            <form method="POST" action="{{ route('investigations.status', $investigation) }}" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="btn btn-success btn-sm d-inline-flex align-items-center" onclick="return confirm('Mark as completed?')">
                    <i class="bi bi-check-circle me-1"></i> Complete
                </button>
            </form>
        @endif
        @if ($investigation->canTransitionTo('cancelled') && auth()->user()->can('investigation.edit'))
            <form method="POST" action="{{ route('investigations.status', $investigation) }}" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center" onclick="return confirm('Cancel this investigation?')">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
            </form>
        @endif
        @can('investigation.view')
            <a href="{{ route('print.investigation', $investigation) }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-printer me-1"></i> Print
            </a>
        @endcan
        <a href="{{ route('investigations.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0"><i class="bi bi-clipboard2-data me-2"></i>Investigation Information</h6>
                    <div class="d-flex gap-2">
                        <span class="badge {{ $investigation->getPriorityBadgeClass() }}">{{ ucfirst($investigation->priority) }}</span>
                        <span class="badge {{ $investigation->getStatusBadgeClass() }}">{{ $investigation->getStatusLabel() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Patient</label>
                            <div class="fw-semibold">{{ $investigation->patient->name ?? '-' }}</div>
                            <div class="small text-muted">{{ $investigation->patient->patient_number ?? '' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Doctor</label>
                            <div>Dr. {{ $investigation->doctor->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Lab Test</label>
                            <div class="fw-semibold">{{ $investigation->labTest->name ?? '-' }}</div>
                            <div class="small text-muted">{{ $investigation->labTest->code ?? '' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Requested Date</label>
                            <div>{{ $investigation->requested_date ? fmt_date($investigation->requested_date) : '-' }}</div>
                        </div>
                        @if ($investigation->consultation)
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Related Consultation</label>
                                <div>
                                    <a href="{{ route('consultations.show', $investigation->consultation) }}" class="text-decoration-none">
                                        Consultation #{{ $investigation->consultation->id }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if ($investigation->clinical_notes)
                            <div class="col-12">
                                <label class="form-label text-muted small mb-0">Clinical Notes</label>
                                <div class="border rounded p-3 bg-light small">{{ $investigation->clinical_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Results Section --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-clipboard2-check me-2"></i>Test Results</h6>
                </div>
                <div class="card-body">
                    @if ($investigation->result_value)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Result Value</label>
                                <div class="fw-semibold">{{ $investigation->result_value }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Unit</label>
                                <div>{{ $investigation->result_unit ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Reference Range</label>
                                <div>{{ $investigation->result_reference_range ?? $investigation->labTest->reference_range ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Resulted At</label>
                                <div>{{ $investigation->resulted_at ? fmt_datetime($investigation->resulted_at) : '-' }}</div>
                            </div>
                            @if ($investigation->interpretation)
                                <div class="col-12">
                                    <label class="form-label text-muted small mb-0">Interpretation</label>
                                    <div class="border rounded p-3 bg-light small">{{ $investigation->interpretation }}</div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            @if (auth()->user()->can('investigation.edit') && ($investigation->isInProgress() || $investigation->isRequested()))
                                <p class="text-muted mb-3">No results entered yet.</p>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#resultModal">
                                    <i class="bi bi-pencil-square me-1"></i> Enter Result
                                </button>
                            @else
                                <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">Results will appear here once entered.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Test Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label text-muted small mb-0">Sample Type</label>
                        <div>{{ $investigation->labTest->sample_type ?? '-' }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small mb-0">Unit</label>
                        <div>{{ $investigation->labTest->unit ?? '-' }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small mb-0">Reference Range</label>
                        <div>{{ $investigation->labTest->reference_range ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="form-label text-muted small mb-0">Price</label>
                        <div class="fw-semibold">${{ number_format($investigation->labTest->price, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Result Entry Modal --}}
    @if (auth()->user()->can('investigation.edit') && ($investigation->isInProgress() || $investigation->isRequested()))
        <div class="modal fade" id="resultModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('investigations.result', $investigation) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Enter Test Result</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Result Value <span class="text-danger">*</span></label>
                                <input type="text" name="result_value" class="form-control" required placeholder="e.g., 12.5 g/dL">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Unit</label>
                                    <input type="text" name="result_unit" class="form-control" value="{{ $investigation->labTest->unit }}" placeholder="e.g., g/dL">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Reference Range</label>
                                    <input type="text" name="result_reference_range" class="form-control" value="{{ $investigation->labTest->reference_range }}" placeholder="e.g., 12-16 g/dL">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Interpretation / Notes</label>
                                <textarea name="interpretation" class="form-control" rows="3" placeholder="Clinical interpretation if needed..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Save Result
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-auth-layout>
