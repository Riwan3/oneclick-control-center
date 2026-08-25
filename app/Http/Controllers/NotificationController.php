<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

/**
 * Every query here is scoped through $request->user()->notifications()
 * (a MorphMany keyed to that specific authenticated user row) — there
 * is no user_id input anywhere, so a user can never read, count, or
 * mark another user's notifications.
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 20), 50);

        $query = $request->user()->notifications();

        $status = $request->string('status', 'all')->toString();
        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        $type = $request->string('type', 'all')->toString();
        if ($type !== 'all' && $type !== '') {
            $query->where('type', $type);
        }

        $notifications = $query->orderByDesc('created_at')->paginate($perPage);

        return NotificationResource::collection($notifications);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json([
            'data' => new NotificationResource($notification->fresh()),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
            'unreadCount' => 0,
        ]);
    }
}
