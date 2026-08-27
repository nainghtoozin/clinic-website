<x-auth-layout>
    <x-page-header title="Backup Details" subtitle="{{ $backup->backup_number }}"
        :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.clinic')], ['label' => 'Backup & Restore', 'url' => route('backups.index')], ['label' => $backup->backup_number]]">
        <div class="d-flex gap-2">
            @if ($backup->isCompleted())
                <a href="{{ route('backups.download', $backup) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i> Download
                </a>
            @endif
            @if ($backup->isCompleted() && !$backup->is_safety)
                @can('backup.restore')
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#restoreModal">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                    </button>
                @endcan
            @endif
            @if ($backup->isCompleted() && !$backup->is_safety)
                @can('backup.delete')
                    <form method="POST" action="{{ route('backups.destroy', $backup) }}" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this backup? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                @endcan
            @endif
        </div>
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

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Backup Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Backup Number</div>
                            <div class="fw-semibold">{{ $backup->backup_number }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Status</div>
                            <span class="badge {{ $backup->status_badge_class }}">{{ $backup->status_label }}</span>
                            @if ($backup->is_safety)
                                <span class="badge bg-secondary ms-1">Safety Backup</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Type</div>
                            <span class="badge {{ $backup->type_badge_class }}">{{ $backup->type_label }}</span>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">File Size</div>
                            <div class="fw-semibold">{{ $backup->formatted_size }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Filename</div>
                            <div class="fw-semibold small">{{ $backup->filename }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Created</div>
                            <div class="small">{{ $backup->created_at->format('M d, Y H:i:s') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Created By</div>
                            <div class="small">{{ $backup->createdBy?->name ?? '-' }}</div>
                        </div>
                        @if ($backup->notes)
                            <div class="col-12">
                                <div class="text-muted small">Notes</div>
                                <div class="small">{{ $backup->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($backupInfo)
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-zip me-2"></i>Archive Contents</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="text-muted small">Application</div>
                                <div class="fw-semibold">{{ $backupInfo['app_name'] ?? '-' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">PHP Version</div>
                                <div class="small">{{ $backupInfo['php_version'] ?? '-' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Laravel Version</div>
                                <div class="small">{{ $backupInfo['laravel_version'] ?? '-' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Database Driver</div>
                                <div class="small">{{ $backupInfo['database_driver'] ?? '-' }}</div>
                            </div>
                            @if (!empty($backupInfo['table_counts']))
                                <div class="col-12">
                                    <div class="text-muted small mb-2">Table Record Counts</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr><th>Table</th><th class="text-end">Records</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($backupInfo['table_counts'] as $table => $count)
                                                    <tr>
                                                        <td class="small">{{ $table }}</td>
                                                        <td class="text-end small">{{ $count >= 0 ? number_format($count) : 'Error' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($backup->metadata && isset($backup->metadata['error']))
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error Details</h6>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0 small bg-light p-3 rounded">{{ $backup->metadata['error'] }}</pre>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>Validation</h6>
                </div>
                <div class="card-body text-center">
                    <div id="validationResult">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="validateBackup()">
                            <i class="bi bi-check2-circle me-1"></i> Validate Backup
                        </button>
                    </div>
                </div>
            </div>

            @if ($backup->isCompleted() && !$backup->is_safety)
                @can('backup.restore')
                    <div class="card shadow-sm border-0 mt-3">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Restore Warning</h6>
                        </div>
                        <div class="card-body">
                            <ul class="small mb-0">
                                <li>Restore will overwrite current data</li>
                                <li>A safety backup is created before restore</li>
                                <li>Foreign key constraints are managed</li>
                                <li>Uploaded files will be replaced</li>
                                <li>This action requires confirmation</li>
                            </ul>
                        </div>
                    </div>
                @endcan
            @endif

            @if ($backup->metadata && isset($backup->metadata['restored_at']))
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-success"><i class="bi bi-check-circle me-2"></i>Restore History</h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <div class="text-muted">Restored at</div>
                            <div>{{ $backup->metadata['restored_at'] }}</div>
                        </div>
                        @if (isset($backup->metadata['safety_backup']))
                            <div class="small mt-2">
                                <div class="text-muted">Safety backup</div>
                                <div>{{ $backup->metadata['safety_backup'] }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($backup->isCompleted() && !$backup->is_safety)
        @can('backup.restore')
            <div class="modal fade" id="restoreModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('backups.restore', $backup) }}">
                            @csrf
                            <div class="modal-header bg-warning bg-opacity-10">
                                <h6 class="modal-title text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Restore Backup</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-3">You are about to restore backup <strong>{{ $backup->backup_number }}</strong>. This will:</p>
                                <ul class="mb-3">
                                    <li>Overwrite the current database with backup data</li>
                                    <li>Replace uploaded files with backup copies</li>
                                    <li>Automatically create a safety backup first</li>
                                </ul>
                                <div class="alert alert-danger py-2 mb-3">
                                    <small><strong>Warning:</strong> All current data will be replaced. This cannot be undone without the safety backup.</small>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="confirm" id="confirmRestore" value="1" required>
                                    <label class="form-check-label" for="confirmRestore">
                                        I understand the risks and want to proceed with this restore
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning btn-sm" id="restoreBtn">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Backup
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    @push('scripts')
    <script>
        function validateBackup() {
            const result = document.getElementById('validationResult');
            result.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span> Validating...';

            fetch('{{ route("backups.validate", $backup) }}')
                .then(r => r.json())
                .then(data => {
                    if (data.valid) {
                        result.innerHTML = '<div class="text-success"><i class="bi bi-check-circle fs-4 d-block mb-2"></i><small class="fw-semibold">Backup is valid and ready for restore.</small></div>';
                    } else {
                        result.innerHTML = '<div class="text-danger"><i class="bi bi-x-circle fs-4 d-block mb-2"></i><small class="fw-semibold">Validation failed:</small><ul class="small mt-2 mb-0 text-start">' + data.errors.map(e => '<li>' + e + '</li>').join('') + '</ul></div>';
                    }
                })
                .catch(() => {
                    result.innerHTML = '<div class="text-danger"><i class="bi bi-x-circle fs-4 d-block mb-2"></i><small>Validation request failed.</small></div>';
                });
        }

        document.getElementById('restoreBtn')?.addEventListener('submit', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Restoring...';
        });
    </script>
    @endpush
</x-auth-layout>
