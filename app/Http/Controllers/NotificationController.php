<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(15);

        // Gather all unique performer IDs and post IDs from notifications to batch load them
        $performerIds = [];
        $postIds = [];
        foreach ($notifications as $notification) {
            $data = $notification->data;
            $performerId = $data['follower_id'] ?? $data['liker_id'] ?? $data['commenter_id'] ?? $data['mentioner_id'] ?? null;
            if ($performerId) {
                $performerIds[] = $performerId;
            }
            if (isset($data['post_id'])) {
                $postIds[] = $data['post_id'];
            }
        }

        $performerIds = array_unique($performerIds);
        $postIds = array_unique($postIds);

        // Fetch performers with their profiles in one query
        $performers = \App\Models\User::whereIn('id', $performerIds)
            ->with('profile')
            ->get()
            ->keyBy('id');

        // Fetch posts in one query
        $posts = \App\Models\Post::whereIn('id', $postIds)
            ->get()
            ->keyBy('id');

        return view('notifications.index', compact('notifications', 'performers', 'posts'));
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
        
        // Gather all performer IDs
        $performerIds = $unread->map(function ($notification) {
            $data = $notification->data;
            return $data['follower_id'] ?? $data['liker_id'] ?? $data['commenter_id'] ?? $data['mentioner_id'] ?? null;
        })->filter()->unique();

        // Fetch all performer users in ONE query, eager loading their profile
        $performers = \App\Models\User::whereIn('id', $performerIds)
            ->with('profile')
            ->get()
            ->keyBy('id');
        
        $data = $unread->map(function ($notification) use ($performers) {
            $data = $notification->data;
            $performerId = $data['follower_id'] ?? $data['liker_id'] ?? $data['commenter_id'] ?? $data['mentioner_id'] ?? null;
            $performerName = 'Someone';
            $performerAvatar = asset('images/default-avatar.png');
            
            if ($performerId && isset($performers[$performerId])) {
                $performer = $performers[$performerId];
                $performerName = $performer->name;
                $performerAvatar = $performer->profile?->avatar_url ?? asset('images/default-avatar.png');
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

    public function goAndMarkAsRead($id)
    {
        $notification = auth()->user()->unreadNotifications()->find($id);
        $targetUrl = '/feed';

        if ($notification) {
            $data = $notification->data;
            if ($notification->type === 'App\Notifications\NewFollowNotification') {
                $performerId = $data['follower_id'] ?? null;
                if ($performerId) {
                    $performer = \App\Models\User::find($performerId);
                    if ($performer) {
                        $targetUrl = route('profile.show', $performer);
                    }
                }
            } elseif (in_array($notification->type, [
                'App\Notifications\NewLikeNotification',
                'App\Notifications\NewCommentNotification',
                'App\Notifications\UserMentionedNotification'
            ])) {
                if (isset($data['post_id'])) {
                    $post = \App\Models\Post::with('user')->find($data['post_id']);
                    if ($post) {
                        $targetUrl = route('profile.show', $post->user) . '#post-' . $post->id;
                    }
                }
            }
            $notification->markAsRead();
        } else {
            $notification = auth()->user()->notifications()->find($id);
            if ($notification) {
                $data = $notification->data;
                if ($notification->type === 'App\Notifications\NewFollowNotification') {
                    $performerId = $data['follower_id'] ?? null;
                    if ($performerId) {
                        $performer = \App\Models\User::find($performerId);
                        if ($performer) {
                            $targetUrl = route('profile.show', $performer);
                        }
                    }
                } elseif (in_array($notification->type, [
                    'App\Notifications\NewLikeNotification',
                    'App\Notifications\NewCommentNotification',
                    'App\Notifications\UserMentionedNotification'
                ])) {
                    if (isset($data['post_id'])) {
                        $post = \App\Models\Post::with('user')->find($data['post_id']);
                        if ($post) {
                            $targetUrl = route('profile.show', $post->user) . '#post-' . $post->id;
                        }
                    }
                }
            }
        }

        return redirect($targetUrl);
    }
}
