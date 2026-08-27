<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    public static function notify(
        User|int $recipient,
        string $type,
        string $title,
        string $message,
        ?Model $notifiable = null,
        ?string $module = null,
        ?string $action = null,
        ?string $url = null,
        ?array $metadata = null
    ): ?Notification {
        $userId = $recipient instanceof User ? $recipient->id : $recipient;

        if (!self::shouldNotify($userId, $type)) {
            return null;
        }

        if (self::isDuplicate($userId, $type, $notifiable, $action)) {
            return null;
        }

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'module' => $module ?? $type,
            'action' => $action,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->getKey(),
            'url' => $url,
            'metadata' => $metadata,
        ]);
    }

    public static function notifyMany(
        array|int $recipients,
        string $type,
        string $title,
        string $message,
        ?Model $notifiable = null,
        ?string $module = null,
        ?string $action = null,
        ?string $url = null,
        ?array $metadata = null
    ): array {
        $recipientIds = is_array($recipients) ? $recipients : [$recipients];
        $notifications = [];

        foreach ($recipientIds as $recipientId) {
            $notification = self::notify(
                $recipientId,
                $type,
                $title,
                $message,
                $notifiable,
                $module,
                $action,
                $url,
                $metadata
            );

            if ($notification) {
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    public static function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?Model $notifiable = null,
        ?string $module = null,
        ?string $action = null,
        ?string $url = null,
        ?array $metadata = null
    ): array {
        $adminIds = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['super-admin', 'admin']);
        })->pluck('id')->toArray();

        return self::notifyMany($adminIds, $type, $title, $message, $notifiable, $module, $action, $url, $metadata);
    }

    public static function unreadCount(User|int $recipient): int
    {
        $userId = $recipient instanceof User ? $recipient->id : $recipient;

        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public static function markAllRead(User|int $recipient): int
    {
        $userId = $recipient instanceof User ? $recipient->id : $recipient;

        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    protected static function shouldNotify(int $userId, string $type): bool
    {
        $user = User::find($userId);
        if (!$user || !$user->is_active) {
            return false;
        }

        return true;
    }

    protected static function isDuplicate(int $userId, string $type, ?Model $notifiable, ?string $action): bool
    {
        if (!$notifiable || !$action) {
            return false;
        }

        return Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->getKey())
            ->where('action', $action)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
    }
}
