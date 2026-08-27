<x-auth-layout>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('audit-logs.index') }}">Audit Logs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
            <h4 class="page-title mb-0">Audit Log #{{ $auditLog->id }}</h4>
        </div>
        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Audit Logs
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0">Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted small mb-0">User</label>
                            <div class="fw-semibold">{{ $auditLog->user?->name ?? 'System' }}</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-0">Date/Time</label>
                            <div class="fw-medium">{{ $auditLog->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-0">Action</label>
                            <div><span class="badge {{ $auditLog->action_badge_class }}">{{ $auditLog->action_label }}</span></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-0">Module</label>
                            <div class="fw-medium">{{ $auditLog->module_label }}</div>
                        </div>
                        @if($auditLog->auditable_type)
                            <div class="col-6">
                                <label class="form-label text-muted small mb-0">Record</label>
                                <div class="fw-medium">{{ class_basename($auditLog->auditable_type) }} #{{ $auditLog->auditable_id }}</div>
                            </div>
                        @endif
                        @if($auditLog->ip_address)
                            <div class="col-6">
                                <label class="form-label text-muted small mb-0">IP Address</label>
                                <div class="fw-medium">{{ $auditLog->ip_address }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0">Description</h6>
                </div>
                <div class="card-body">
                    {{ $auditLog->description ?? 'No description' }}
                </div>
            </div>
        </div>

        @if($auditLog->formatted_changes)
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0">Changes</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Field</th>
                                    <th>Previous Value</th>
                                    <th>New Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditLog->formatted_changes as $key => $change)
                                    <tr>
                                        <td class="fw-medium">{{ $key }}</td>
                                        <td class="text-muted">{{ $change['old'] ?? '—' }}</td>
                                        <td>{{ $change['new'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-auth-layout>
