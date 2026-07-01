<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->unreadNotifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function apiGetUnread()
    {
        $unread = auth()->user()->unreadNotifications;
        
        $data = $unread->map(function ($notification) {
            $data = $notification->data;
            $performerId = $data['follower_id'] ?? $data['liker_id'] ?? $data['commenter_id'] ?? null;
            $performerName = 'Someone';
            $performerAvatar = asset('images/default-avatar.png');
            
            if ($performerId) {
                $performer = \App\Models\User::find($performerId);
                if ($performer) {
                    $performerName = $performer->name;
                    $performerAvatar = $performer->profile?->avatar_url ?? asset('images/default-avatar.png');
                }
            }
            
            return [
                'id' => $notification->id,
                'message' => $data['message'] ?? '',
                'performer_name' => $performerName,
                'performer_avatar' => $performerAvatar,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json($data);
    }
}
