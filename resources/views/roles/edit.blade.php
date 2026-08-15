<x-auth-layout>
    <x-page-header title="Edit Role" subtitle="{{ $role->name }}"
        :breadcrumbs="[['label' => 'Roles & Permissions', 'url' => route('roles.index')], ['label' => $role->name], ['label' => 'Edit']]">
        <a href="{{ route('roles.show', $role) }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-eye me-1"></i> View
        </a>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Roles
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

    <form method="POST" action="{{ route('roles.update', $role) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Role Details</h6>
            </div>
            <div class="card-body">
                <div class="col-md-6">
                    <label class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $role->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Use lowercase with hyphens (e.g., <code>front-desk</code>).</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>Permissions</h6>
            </div>
            <div class="card-body">
                @include('roles.partials.permissions', ['selected' => $rolePermissions])
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-warning">
                <i class="bi bi-check-lg me-1"></i> Update Role
            </button>
        </div>
    </form>
</x-auth-layout>
