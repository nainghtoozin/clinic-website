<x-auth-layout>
    <x-page-header title="Staff Profile" subtitle="{{ $staff->position ?? 'Staff member' }}"
        :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => $staff->name]]">
        @can('staff.edit')
            <a href="{{ route('staff.edit', $staff) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Staff
        </a>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Profile header --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        @if ($staff->avatar)
                            <img src="{{ Storage::url($staff->avatar) }}" alt="{{ $staff->name }}"
                                class="avatar" style="width:64px;height:64px;border-radius:14px;font-size:1.3rem;">
                        @else
                            <span class="avatar bg-primary" style="width:64px;height:64px;border-radius:14px;font-size:1.3rem;">
                                {{ initials($staff->name) }}
                            </span>
                        @endif
                        <div class="flex-grow-1 min-w-0">
                            <h5 class="mb-1">{{ $staff->name }}</h5>
                            <div class="text-muted small mb-2">{{ $staff->email }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge {{ $staff->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    <span class="status-dot"></span>{{ $staff->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @foreach ($staff->roles as $role)
                                    <span class="badge {{ match ($role->name) {
                                        'super-admin' => 'bg-danger',
                                        'admin' => 'bg-primary',
                                        'doctor' => 'bg-success',
                                        'nurse' => 'bg-info text-dark',
                                        'receptionist' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    } }}">
                                        <span class="status-dot"></span>{{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                                @if ($staff->roles->isEmpty())
                                    <span class="text-muted small">No role assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <label class="form-label text-muted small mb-0">Phone</label>
                            <div class="fw-semibold">{{ $staff->phone ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label text-muted small mb-0">Position</label>
                            <div class="fw-semibold">{{ $staff->position ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label text-muted small mb-0">Registered</label>
                            <div class="fw-semibold">{{ fmt_date($staff->created_at) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($staff->doctor)
                <div class="card shadow-sm border-0 mb-4 border-start border-4 border-success">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Linked Doctor Profile</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            @if ($staff->doctor->profile_image)
                                <img src="{{ Storage::url($staff->doctor->profile_image) }}" alt="{{ $staff->doctor->name }}"
                                    class="avatar">
                            @else
                                <span class="avatar bg-success">{{ initials($staff->doctor->name) }}</span>
                            @endif
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold">{{ $staff->doctor->name }}</div>
                                <div class="text-muted small">{{ $staff->doctor->department->name ?? '-' }}</div>
                            </div>
                            @can('doctor.view')
                                <a href="{{ route('doctors.show', $staff->doctor) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Access</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Role</label>
                        <div>
                            @foreach ($staff->roles as $role)
                                <span class="badge bg-primary-subtle text-primary">{{ ucfirst($role->name) }}</span>
                            @endforeach
                            @if ($staff->roles->isEmpty())
                                <span class="text-muted">None</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-0">Account Status</label>
                        <div>
                            <span class="badge {{ $staff->is_active ? 'bg-success' : 'bg-secondary' }}">
                                <span class="status-dot"></span>{{ $staff->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Activity</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted small">Registered</span>
                        <span class="small">{{ fmt_datetime($staff->created_at) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Last Updated</span>
                        <span class="small">{{ fmt_datetime($staff->updated_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
