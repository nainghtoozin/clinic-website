<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">User Details</h5>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Name</label>
                    <div class="fw-semibold">{{ $user->name }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Email</label>
                    <div>{{ $user->email }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Role</label>
                    <div>
                        @foreach ($user->roles as $role)
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        @endforeach
                        @if ($user->roles->isEmpty())
                            <span class="text-muted">No role assigned</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Registered</label>
                    <div>{{ fmt_datetime($user->created_at) }}</div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
