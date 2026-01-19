<div class="modal fade" id="viewDoctor{{ $doctor->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">

            {{-- Header --}}
            <div class="modal-header bg-primary text-white">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $doctor->profile_image
                        ? asset('storage/' . $doctor->profile_image)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($doctor->name) }}"
                        class="rounded-circle border border-2 border-white" width="60" height="60" alt="Doctor">

                    <div>
                        <h5 class="mb-0 fw-bold">{{ $doctor->name }}</h5>
                        <small class="opacity-75">
                            {{ $doctor->title ?? 'Medical Specialist' }}
                        </small>
                    </div>
                </div>

                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-light">
                <div class="container-fluid">
                    <div class="row g-4">

                        {{-- LEFT INFO --}}
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">Professional Info</h6>

                                    <p class="mb-2">
                                        <strong>Role:</strong><br>
                                        <span class="text-muted">{{ $doctor->role ?? '-' }}</span>
                                    </p>

                                    <p class="mb-2">
                                        <strong>Department:</strong><br>
                                        <span class="text-muted">
                                            {{ $doctor->department?->name ?? ($doctor->primary_department ?? '-') }}
                                        </span>
                                    </p>

                                    <p class="mb-2">
                                        <strong>Qualifications:</strong><br>
                                        <span class="text-muted">{{ $doctor->qualifications ?? '-' }}</span>
                                    </p>

                                    <p class="mb-2">
                                        <strong>Experience:</strong><br>
                                        <span class="text-muted">
                                            {{ $doctor->experience_years }} years
                                        </span>
                                    </p>

                                    <div class="mt-3">
                                        @if ($doctor->is_available)
                                            <span class="badge bg-success">Available</span>
                                        @else
                                            <span class="badge bg-secondary">Unavailable</span>
                                        @endif

                                        @if ($doctor->board_certified)
                                            <span class="badge bg-info text-dark">Board Certified</span>
                                        @endif

                                        @if ($doctor->is_featured)
                                            <span class="badge bg-warning text-dark">Featured</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT INFO --}}
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">About Doctor</h6>

                                    @if ($doctor->short_description)
                                        <p class="fw-semibold">
                                            {{ $doctor->short_description }}
                                        </p>
                                        <hr>
                                    @endif

                                    <p class="text-muted" style="line-height: 1.7">
                                        {{ $doctor->biography ?? 'No biography available.' }}
                                    </p>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Gender:</strong>
                                            <span class="text-muted">
                                                {{ ucfirst($doctor->gender ?? '-') }}
                                            </span>
                                        </div>

                                        <div class="col-md-6">
                                            <strong>Location:</strong>
                                            <span class="text-muted">
                                                {{ $doctor->location ?? '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    @if ($doctor->availability_note)
                                        <div class="alert alert-info mt-3 mb-0">
                                            <strong>Availability Note:</strong><br>
                                            {{ $doctor->availability_note }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-white">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-primary">
                    Edit Doctor
                </a>
            </div>

        </div>
    </div>
</div>
