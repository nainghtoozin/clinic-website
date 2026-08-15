<x-auth-layout>
    <x-page-header title="Create Role" subtitle="Define a new role and its permissions"
        :breadcrumbs="[['label' => 'Roles & Permissions', 'url' => route('roles.index')], ['label' => 'Create Role']]">
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

    <form method="POST" action="{{ route('roles.store') }}" novalidate>
        @csrf

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Role Details</h6>
            </div>
            <div class="card-body">
                <div class="col-md-6">
                    <label class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        placeholder="Doctor, Nurse, Receptionist" value="{{ old('name') }}" required>
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
                @include('roles.partials.permissions', ['selected' => []])
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Create Role
            </button>
        </div>
    </form>
</x-auth-layout>
