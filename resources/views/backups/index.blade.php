<x-auth-layout>
    <x-page-header title="Backup & Restore" subtitle="Create and manage database and file backups"
        :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.clinic')], ['label' => 'Backup & Restore']]">
        @can('backup.create')
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                <i class="bi bi-plus-circle me-1"></i> Create Backup
            </button>
        @endcan
    </x-page-header>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-database text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Backups</div>
                            <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Completed</div>
                            <div class="fs-4 fw-bold">{{ $stats['completed'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-hdd text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Size</div>
                            <div class="fs-4 fw-bold">
                                @php $bytes = $stats['total_size']; @endphp
                                @if ($bytes >= 1073741824)
                                    {{ round($bytes / 1073741824, 2) }} GB
                                @elseif ($bytes >= 1048576)
                                    {{ round($bytes / 1048576, 2) }} MB
                                @elseif ($bytes >= 1024)
                                    {{ round($bytes / 1024, 2) }} KB
                                @else
                                    {{ $bytes }} B
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('backups.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Type</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            @foreach (\App\Models\Backup::TYPES as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            @foreach (\App\Models\Backup::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i></button>
                        @if (request()->hasAny(['type', 'status']))
                            <a href="{{ route('backups.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($backups->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-database fs-1 text-muted d-block mb-2"></i>
                    <p class="text-muted mb-3">No backups found.</p>
                    @can('backup.create')
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                            <i class="bi bi-plus-circle me-1"></i> Create Your First Backup
                        </button>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Backup Number</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th class="text-end">Size</th>
                                <th>Created</th>
                                <th>By</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($backups as $backup)
                                <tr>
                                    <td>
                                        <a href="{{ route('backups.show', $backup) }}" class="fw-semibold text-decoration-none">
                                            {{ $backup->backup_number }}
                                        </a>
                                        @if ($backup->is_safety)
                                            <span class="badge bg-secondary ms-1">Safety</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $backup->type_badge_class }}">{{ $backup->type_label }}</span></td>
                                    <td><span class="badge {{ $backup->status_badge_class }}">{{ $backup->status_label }}</span></td>
                                    <td class="text-end">{{ $backup->formatted_size }}</td>
                                    <td class="small text-muted">{{ $backup->created_at->diffForHumans() }}</td>
                                    <td class="small text-muted">{{ $backup->createdBy?->name ?? '-' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('backups.show', $backup) }}" class="btn btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if ($backup->isCompleted())
                                                <a href="{{ route('backups.download', $backup) }}" class="btn btn-outline-success" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @endif
                                            @if ($backup->isCompleted() && !$backup->is_safety)
                                                @can('backup.delete')
                                                    <form method="POST" action="{{ route('backups.destroy', $backup) }}" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this backup? This action cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($backups->hasPages())
                    <div class="card-footer bg-white">
                        {{ $backups->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    @can('backup.create')
        <div class="modal fade" id="createBackupModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('backups.store') }}" id="createBackupForm">
                        @csrf
                        <div class="modal-header">
                            <h6 class="modal-title"><i class="bi bi-database me-2"></i>Create Backup</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Backup Type</label>
                                <div class="d-flex gap-3">
                                    @foreach (\App\Models\Backup::TYPES as $key => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" id="type_{{ $key }}" value="{{ $key }}" {{ $key === 'full' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="type_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-text">
                                    <strong>Full:</strong> Database + uploaded files<br>
                                    <strong>Database:</strong> Database only<br>
                                    <strong>Files:</strong> Uploaded files only
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Reason for this backup..."></textarea>
                            </div>
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <small>Backup includes sensitive clinic data. Handle with care.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="createBackupBtn">
                                <i class="bi bi-database me-1"></i> Create Backup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @push('scripts')
    <script>
        document.getElementById('createBackupForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('createBackupBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';
        });
    </script>
    @endpush
</x-auth-layout>
