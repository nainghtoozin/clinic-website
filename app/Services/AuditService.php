<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public static function log(
        string $action,
        string $module,
        ?Model $model = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): ?AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues ? AuditLog::sanitizeValues($oldValues) : null,
            'new_values' => $newValues ? AuditLog::sanitizeValues($newValues) : null,
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    public static function logCreated(Model $model, string $module, ?string $description = null): ?AuditLog
    {
        return self::log(
            'created',
            $module,
            $model,
            $description ?? self::defaultDescription('created', $module, $model),
            null,
            $model->getAttributes()
        );
    }

    public static function logUpdated(Model $model, string $module, array $oldValues, array $newValues, ?string $description = null): ?AuditLog
    {
        $filteredOld = AuditLog::sanitizeValues($oldValues);
        $filteredNew = AuditLog::sanitizeValues($newValues);

        $changedOld = [];
        $changedNew = [];
        foreach (array_keys($filteredNew) as $key) {
            if (array_key_exists($key, $filteredOld) && $filteredOld[$key] != $filteredNew[$key]) {
                $changedOld[$key] = $filteredOld[$key];
                $changedNew[$key] = $filteredNew[$key];
            }
        }

        if (empty($changedNew)) {
            return null;
        }

        return self::log(
            'updated',
            $module,
            $model,
            $description ?? self::defaultDescription('updated', $module, $model),
            $changedOld,
            $changedNew
        );
    }

    public static function logDeleted(Model $model, string $module, ?string $description = null): ?AuditLog
    {
        return self::log(
            'deleted',
            $module,
            $model,
            $description ?? self::defaultDescription('deleted', $module, $model),
            $model->getAttributes(),
            null
        );
    }

    public static function logRestored(Model $model, string $module, ?string $description = null): ?AuditLog
    {
        return self::log(
            'restored',
            $module,
            $model,
            $description ?? self::defaultDescription('restored', $module, $model),
            null,
            $model->getAttributes()
        );
    }

    public static function logStatusChange(Model $model, string $module, string $oldStatus, string $newStatus, ?string $description = null): ?AuditLog
    {
        return self::log(
            'status_changed',
            $module,
            $model,
            $description ?? class_basename($module) . " status changed from \"{$oldStatus}\" to \"{$newStatus}\"",
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );
    }

    public static function logLogin(): ?AuditLog
    {
        return self::log('login', 'Auth', null, 'User logged in');
    }

    public static function logLogout(): ?AuditLog
    {
        return self::log('logout', 'Auth', null, 'User logged out');
    }

    public static function logRoleChanged(int $userId, array $oldRoles, array $newRoles): ?AuditLog
    {
        return self::log(
            'role_changed',
            'User',
            null,
            'Role assignments changed for user #' . $userId,
            ['roles' => $oldRoles],
            ['roles' => $newRoles]
        );
    }

    public static function logPermissionChanged(int $userId, array $oldPermissions, array $newPermissions): ?AuditLog
    {
        return self::log(
            'permission_changed',
            'User',
            null,
            'Direct permissions changed for user #' . $userId,
            ['permissions' => $oldPermissions],
            ['permissions' => $newPermissions]
        );
    }

    protected static function defaultDescription(string $action, string $module, Model $model): string
    {
        $label = class_basename($module);
        $id = $model->getKey();

        $name = null;
        if (method_exists($model, 'getAttribute')) {
            foreach (['name', 'full_name', 'patient_number', 'backup_number', 'invoice_number', 'prescription_number', 'email'] as $attr) {
                if ($model->getAttribute($attr)) {
                    $name = $model->getAttribute($attr);
                    break;
                }
            }
        }

        $display = $name ? "\"{$name}\" (#{$id})" : "#{$id}";

        return match ($action) {
            'created' => "{$label} {$display} created",
            'updated' => "{$label} {$display} updated",
            'deleted' => "{$label} {$display} deleted",
            'restored' => "{$label} {$display} restored",
            default => "{$label} {$display} {$action}",
        };
    }
}
