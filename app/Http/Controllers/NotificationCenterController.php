<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $query = Notification::where('user_id', $user->id);

        if ($request->filled('filter')) {
            match ($request->filter) {
                'unread' => $query->unread(),
                'read' => $query->where('is_read', true),
                default => null,
            };
        }

        if ($request->filled('module')) {
            $query->forModule($request->module);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $notifications = $query->latest()->paginate(20)->withQueryString();
        $unreadCount = NotificationService::unreadCount($user);
        $modules = Notification::TYPES;

        return view('notifications.index', compact('notifications', 'unreadCount', 'modules'));
    }

    public function show(Request $request, Notification $notification): View|JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->load('user');
        $notifiable = $notification->notifiable();

        if ($request->ajax()) {
            $notification->markAsRead();

            return response()->json([
                'id' => $notification->id,
                'type' => $notification->type,
                'type_label' => $notification->type_label,
                'icon' => $notification->icon,
                'title' => $notification->title,
                'message' => $notification->message,
                'module' => $notification->module,
                'action' => $notification->action,
                'url' => $notification->url,
                'metadata' => $notification->metadata,
                'is_read' => true,
                'read_at' => now()->toDateTimeString(),
                'created_at' => $notification->created_at->toDateTimeString(),
            ]);
        }

        $notification->markAsRead();

        if ($notification->url && $notifiable) {
            try {
                if ($request->user()->can('view', $notifiable)) {
                    return redirect($notification->url);
                }
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                // No policy defined, show the notification detail instead
            }
        }

        return view('notifications.show', compact('notification', 'notifiable'));
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::unreadCount($request->user()),
        ]);
    }

    public function markUnread(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::unreadCount($request->user()),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $count = NotificationService::markAllRead($request->user());

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'unread_count' => 0,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => NotificationService::unreadCount($request->user()),
        ]);
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::unreadCount($request->user()),
        ]);
    }
}
