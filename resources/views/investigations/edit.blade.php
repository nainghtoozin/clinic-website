<x-auth-layout>
    <x-page-header title="Edit Investigation" subtitle="{{ $investigation->labTest->name ?? '' }}"
        :breadcrumbs="[['label' => 'Investigations', 'url' => route('investigations.index')], ['label' => 'Details', 'url' => route('investigations.show', $investigation)], ['label' => 'Edit']]">
        <a href="{{ route('investigations.show', $investigation) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
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

    <form method="POST" action="{{ route('investigations.update', $investigation) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-clipboard2-data me-2"></i>Investigation Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Patient</label>
                                <div class="form-control bg-light">{{ $investigation->patient->name ?? '-' }} ({{ $investigation->patient->patient_number ?? '' }})</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Doctor</label>
                                <div class="form-control bg-light">Dr. {{ $investigation->doctor->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Lab Test</label>
                                <div class="form-control bg-light">{{ $investigation->labTest->name ?? '-' }} ({{ $investigation->labTest->code ?? '' }})</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Status</label>
                                <div class="form-control bg-light">
                                    <span class="badge {{ $investigation->getStatusBadgeClass() }}">{{ $investigation->getStatusLabel() }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Clinical Notes</label>
                                <textarea name="clinical_notes" class="form-control @error('clinical_notes') is-invalid @enderror" rows="3">{{ old('clinical_notes', $investigation->clinical_notes) }}</textarea>
                                @error('clinical_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Info</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">Patient, doctor, and test cannot be changed after creation. Only clinical notes can be updated.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('investigations.show', $investigation) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Update Investigation
            </button>
        </div>
    </form>
</x-auth-layout>
