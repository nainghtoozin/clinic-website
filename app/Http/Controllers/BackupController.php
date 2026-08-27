<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Services\AuditService;
use App\Services\BackupService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backupService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('backup.view');

        $backups = Backup::with('createdBy')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Backup::count(),
            'completed' => Backup::where('status', 'completed')->count(),
            'total_size' => Backup::where('status', 'completed')->sum('size'),
        ];

        return view('backups.index', compact('backups', 'stats'));
    }

    public function store(Request $request)
    {
        Gate::authorize('backup.create');

        $request->validate([
            'type' => 'required|in:full,database,files',
            'notes' => 'nullable|string|max:500',
        ]);

        $backup = $this->backupService->createBackup(
            $request->type,
            $request->notes
        );

        AuditService::log('backup_created', 'Backup', $backup);

        NotificationService::notifyAdmins(
            'backup',
            'Backup Created',
            "Backup {$backup->backup_number} ({$backup->type}) has been created successfully.",
            $backup,
            'backup',
            'created',
            route('backups.show', $backup)
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully.',
                'backup' => $backup,
            ]);
        }

        return redirect()->route('backups.index')
            ->with('success', "Backup {$backup->backup_number} created successfully.");
    }

    public function show(Backup $backup)
    {
        Gate::authorize('backup.view');
        $backup->load('createdBy');

        $filepath = storage_path('app/backups/' . $backup->filename);
        $backupInfo = null;

        if ($backup->isCompleted() && File::exists($filepath)) {
            $backupInfo = $this->backupService->getBackupInfo($filepath);
        }

        return view('backups.show', compact('backup', 'backupInfo'));
    }

    public function download(Backup $backup)
    {
        Gate::authorize('backup.view');

        $filepath = storage_path('app/backups/' . $backup->filename);

        if (!File::exists($filepath)) {
            return redirect()->route('backups.index')
                ->with('error', 'Backup file not found on disk.');
        }

        return response()->download($filepath, $backup->filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function validateBackup(Backup $backup)
    {
        Gate::authorize('backup.view');

        $filepath = storage_path('app/backups/' . $backup->filename);

        if (!File::exists($filepath)) {
            return response()->json(['valid' => false, 'errors' => ['File not found.']], 404);
        }

        $errors = $this->backupService->validateBackup($filepath);

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
        ]);
    }

    public function restore(Request $request, Backup $backup)
    {
        Gate::authorize('backup.restore');

        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        if (!$backup->isCompleted()) {
            return redirect()->route('backups.show', $backup)
                ->with('error', 'Only completed backups can be restored.');
        }

        $filepath = storage_path('app/backups/' . $backup->filename);

        if (!File::exists($filepath)) {
            return redirect()->route('backups.show', $backup)
                ->with('error', 'Backup file not found on disk.');
        }

        $safetyBackup = $this->backupService->createBackup(
            'full',
            "Safety backup before restoring {$backup->backup_number}",
            true
        );

        try {
            $backup->update(['status' => 'restoring']);

            $this->backupService->restoreBackup($backup);

            $backup->update([
                'status' => 'restored',
                'metadata' => array_merge($backup->metadata ?? [], [
                    'restored_at' => now()->toDateTimeString(),
                    'restored_by' => auth()->id(),
                    'safety_backup' => $safetyBackup->backup_number,
                ]),
            ]);

            AuditService::log('backup_restored', 'Backup', $backup);

            return redirect()->route('backups.index')
                ->with('success', "Backup {$backup->backup_number} restored successfully. Safety backup: {$safetyBackup->backup_number}");
        } catch (\Throwable $e) {
            $backup->update(['status' => 'failed']);

            return redirect()->route('backups.show', $backup)
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function destroy(Backup $backup)
    {
        Gate::authorize('backup.delete');

        $backupNumber = $backup->backup_number;
        AuditService::log('backup_deleted', 'Backup', $backup);
        $this->backupService->deleteBackup($backup);

        return redirect()->route('backups.index')
            ->with('success', "Backup {$backupNumber} deleted successfully.");
    }
}
