<x-auth-layout>
    <x-page-header title="Doctor Profile" subtitle="{{ $doctor->title ?? $doctor->role ?? '' }}"
        :breadcrumbs="[['label' => 'Doctors', 'url' => route('doctors.index')], ['label' => $doctor->name]]">
        @can('doctor.edit')
            <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Doctors
        </a>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Profile header --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        @if ($doctor->profile_image)
                            <img src="{{ Storage::url($doctor->profile_image) }}" alt="{{ $doctor->name }}"
                                class="avatar" style="width:64px;height:64px;border-radius:14px;font-size:1.3rem;">
                        @else
                            <span class="avatar bg-primary" style="width:64px;height:64px;border-radius:14px;font-size:1.3rem;">
                                {{ initials($doctor->name) }}
                            </span>
                        @endif
                        <div class="flex-grow-1 min-w-0">
                            <h5 class="mb-1">{{ $doctor->name }}</h5>
                            <div class="text-muted small mb-2">{{ $doctor->title ?? $doctor->role ?? 'Doctor' }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge {{ $doctor->is_available ? 'bg-success' : 'bg-secondary' }}">
                                    <span class="status-dot"></span>{{ $doctor->is_available ? 'Available' : 'Unavailable' }}
                                </span>
                                @if ($doctor->board_certified)
                                    <span class="badge bg-info text-dark"><i class="bi bi-patch-check me-1"></i>Board Certified</span>
                                @endif
                                @if ($doctor->is_featured)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-star me-1"></i>Featured</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- About --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-chat-quote me-2"></i>About Doctor</h6>
                </div>
                <div class="card-body">
                    @if ($doctor->short_description)
                        <p class="fw-semibold">{{ $doctor->short_description }}</p>
                        <hr>
                    @endif
                    <p class="text-muted mb-0" style="line-height:1.7;">
                        {{ $doctor->biography ?? 'No biography available.' }}
                    </p>
                    @if ($doctor->availability_note)
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Availability Note:</strong> {{ $doctor->availability_note }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Professional info --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Professional Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Department</label>
                        <div class="fw-semibold">
                            {{ $doctor->department?->name ?? ($doctor->primary_department ?? '-') }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Role</label>
                        <div class="fw-semibold">{{ $doctor->role ?? '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Qualifications</label>
                        <div>{{ $doctor->qualifications ?? '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Experience</label>
                        <div>{{ $doctor->experience_years ?? 0 }} years</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Gender</label>
                        <div>{{ ucfirst($doctor->gender ?? '-') }}</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-0">Location</label>
                        <div><i class="bi bi-geo-alt me-1 text-muted"></i>{{ $doctor->location ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Working schedule --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Working Schedule</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Working Days</label>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse ($doctor->dayLabels() as $day)
                                <span class="badge bg-primary-subtle text-primary">{{ $day }}</span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-0">Hours</label>
                        @if ($doctor->start_time && $doctor->end_time)
                            <div class="fw-semibold">
                                <i class="bi bi-clock me-1 text-muted"></i>
                                {{ fmt_time($doctor->start_time) }} &ndash; {{ fmt_time($doctor->end_time) }}
                            </div>
                        @else
                            <div class="text-muted">-</div>
                        @endif
                    </div>
                </div>
            </div>

            @can('doctor.edit')
                <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-primary w-100">
                    <i class="bi bi-pencil me-1"></i> Edit Doctor
                </a>
            @endcan
        </div>
    </div>
</x-auth-layout>
