<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('audit.view');

        $query = AuditLog::with('user');

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('module')) {
            $query->forModule($request->module);
        }
        if ($request->filled('action')) {
            $query->forAction($request->action);
        }
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $auditLogs = $query->latest()->paginate(25)->withQueryString();

        $modules = AuditLog::MODULES;
        $actions = AuditLog::ACTIONS;
        $users = \App\Models\User::orderBy('name')->pluck('name', 'id');

        return view('audit-logs.index', compact('auditLogs', 'modules', 'actions', 'users'));
    }

    public function show(Request $request, AuditLog $auditLog)
    {
        Gate::authorize('audit.view');

        $auditLog->load('user');

        if ($request->ajax()) {
            return response()->json([
                'id' => $auditLog->id,
                'user' => $auditLog->user?->name ?? 'System',
                'action' => $auditLog->action_label,
                'action_badge' => $auditLog->action_badge_class,
                'module' => $auditLog->module_label,
                'description' => $auditLog->description,
                'auditable_type' => $auditLog->auditable_type ? class_basename($auditLog->auditable_type) : null,
                'auditable_id' => $auditLog->auditable_id,
                'old_values' => $auditLog->old_values,
                'new_values' => $auditLog->new_values,
                'formatted_changes' => $auditLog->formatted_changes,
                'metadata' => $auditLog->metadata,
                'ip_address' => $auditLog->ip_address,
                'created_at' => $auditLog->created_at->toDateTimeString(),
            ]);
        }

        return view('audit-logs.show', compact('auditLog'));
    }
}
