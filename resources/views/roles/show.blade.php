<x-auth-layout>
    <x-page-header title="Role Details" subtitle="{{ $role->name }}"
        :breadcrumbs="[['label' => 'Roles & Permissions', 'url' => route('roles.index')], ['label' => $role->name]]">
        @can('role.edit')
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Role</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar {{ $role->name === 'super-admin' ? 'bg-danger' : ($role->name === 'admin' ? 'bg-primary' : 'bg-success') }}" style="width:48px;height:48px;font-size:1.1rem;">
                            <i class="bi bi-person-badge"></i>
                        </span>
                        <div>
                            <div class="fw-semibold fs-5">{{ ucfirst($role->name) }}</div>
                            <small class="text-muted">{{ $role->name }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Assigned Users</label>
                        <div class="fw-semibold">
                            <i class="bi bi-people me-1"></i>{{ $role->users_count ?? 0 }} user(s)
                        </div>
                    </div>
                    <div>
                        <label class="form-label text-muted small mb-0">Permissions</label>
                        <div class="fw-semibold">
                            <i class="bi bi-shield-check me-1"></i>{{ $role->permissions->count() }} permission(s)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-grid me-2"></i>Permissions by Group</h6>
                </div>
                <div class="card-body">
                    @php
                        $grouped = $role->permissions->groupBy(fn ($p) => explode('.', $p->name)[0])->sortKeys();
                    @endphp
                    @if ($grouped->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-shield-x fs-1 d-block mb-2"></i>
                            <small>No permissions assigned to this role.</small>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach ($grouped as $group => $permissions)
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary-subtle text-primary text-uppercase">{{ $group }}</span>
                                        <small class="text-muted">{{ $permissions->count() }} permission(s)</small>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mb-1">
                                        @foreach ($permissions as $permission)
                                            <span class="badge bg-light text-dark border">
                                                {{ str_replace($group . '.', '', $permission->name) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
