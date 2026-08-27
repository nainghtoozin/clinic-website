<x-auth-layout>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Audit Logs</li>
                </ol>
            </nav>
            <h4 class="page-title mb-0">Audit Logs</h4>
            <p class="text-muted small mb-0 mt-1">System activity history and audit trail</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('audit-logs.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="User, description, module..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Module</label>
                        <select name="module" class="form-select form-select-sm">
                            <option value="">All Modules</option>
                            @foreach($modules as $key => $label)
                                <option value="{{ $key }}" {{ request('module') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Action</label>
                        <select name="action" class="form-select form-select-sm">
                            <option value="">All Actions</option>
                            @foreach($actions as $key => $label)
                                <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">User</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label small text-muted mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label small text-muted mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th class="d-none d-md-table-cell">Record</th>
                        <th>Description</th>
                        <th class="text-end">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="text-nowrap">
                                <small class="text-muted">{{ $log->created_at->format('M d, Y') }}</small><br>
                                <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $log->user?->name ?? 'System' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $log->action_badge_class }}">
                                    <span class="status-dot"></span> {{ $log->action_label }}
                                </span>
                            </td>
                            <td>{{ $log->module_label }}</td>
                            <td class="d-none d-md-table-cell">
                                @if($log->auditable_type)
                                    <span class="badge bg-light border text-dark">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width:250px;" title="{{ $log->description }}">
                                    {{ $log->description ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#auditDetailModal"
                                    onclick="showAuditDetail({{ $log->id }})">
                                    <i class="bi bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                                No audit logs found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
        <small class="text-muted">
            Showing {{ $auditLogs->firstItem() ?? 0 }}&ndash;{{ $auditLogs->lastItem() ?? 0 }} of {{ $auditLogs->total() }} audit logs
        </small>
        <div>{{ $auditLogs->links() }}</div>
    </div>

    <div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="auditDetailModalLabel"><i class="bi bi-clock-history me-2"></i>Audit Log Detail</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="auditDetailBody">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showAuditDetail(id) {
            const body = document.getElementById('auditDetailBody');
            body.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            fetch('/audit-logs/' + id, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                let html = '<div class="mb-3">';
                html += '<div class="row g-2 mb-3">';
                html += '<div class="col-6"><label class="form-label text-muted small mb-0">User</label><div class="fw-semibold">' + (data.user || 'System') + '</div></div>';
                html += '<div class="col-6"><label class="form-label text-muted small mb-0">Date/Time</label><div class="fw-medium">' + data.created_at + '</div></div>';
                html += '<div class="col-6"><label class="form-label text-muted small mb-0">Action</label><div><span class="badge ' + data.action_badge + '">' + data.action + '</span></div></div>';
                html += '<div class="col-6"><label class="form-label text-muted small mb-0">Module</label><div class="fw-medium">' + data.module + '</div></div>';
                if (data.auditable_type) {
                    html += '<div class="col-6"><label class="form-label text-muted small mb-0">Record</label><div class="fw-medium">' + data.auditable_type + ' #' + data.auditable_id + '</div></div>';
                }
                if (data.ip_address) {
                    html += '<div class="col-6"><label class="form-label text-muted small mb-0">IP Address</label><div class="fw-medium">' + data.ip_address + '</div></div>';
                }
                html += '</div>';

                if (data.description) {
                    html += '<div class="mb-3"><label class="form-label text-muted small mb-1">Description</label><div>' + data.description + '</div></div>';
                }

                if (data.formatted_changes) {
                    html += '<div class="border-top pt-3"><label class="form-label text-muted small mb-2">Changes</label>';
                    html += '<table class="table table-sm table-bordered mb-0">';
                    html += '<thead class="table-light"><tr><th>Field</th><th>Previous</th><th>New</th></tr></thead><tbody>';
                    for (const [key, val] of Object.entries(data.formatted_changes)) {
                        const oldVal = val.old !== null && val.old !== undefined ? String(val.old) : '<span class="text-muted">—</span>';
                        const newVal = val.new !== null && val.new !== undefined ? String(val.new) : '<span class="text-muted">—</span>';
                        html += '<tr><td class="fw-medium">' + key + '</td><td class="text-muted">' + oldVal + '</td><td>' + newVal + '</td></tr>';
                    }
                    html += '</tbody></table></div>';
                }

                html += '</div>';
                body.innerHTML = html;
            })
            .catch(() => {
                body.innerHTML = '<div class="text-center py-3 text-danger">Failed to load audit detail.</div>';
            });
        }
    </script>
    @endpush
</x-auth-layout>
