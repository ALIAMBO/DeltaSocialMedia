<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Post $post)
    {
        $user = Auth::user();
        $like = $post->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            if ($post->user_id !== $user->id) {
                $post->user->notify(new \App\Notifications\NewLikeNotification($user, $post));
                event(new \App\Events\NotificationSent($post->user, [
                    'message' => 'liked your post: "' . \Illuminate\Support\Str::limit($post->body, 30) . '"',
                    'performer_name' => $user->name,
                    'performer_avatar' => $user->profile?->avatar_url ?? asset('images/default-avatar.png'),
                ]));
            }
        }

        if (request()->wantsJson()) {
            return response()->json([
                'liked' => !$like,
                'likes_count' => $post->likes()->count(),
            ]);
        }

        return back();
    }
}
