<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'body'  => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        if (empty($request->body) && !$request->hasFile('image')) {
            return back()->with('error', 'Post must have text or an image.');
        }

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->store('posts', 'public');
            $imageName = basename($imageName);
        }

        $post = Auth::user()->posts()->create([
            'body'  => $request->body,
            'image' => $imageName,
        ]);

        if ($post->body) {
            preg_match_all('/@([\w\-]+)/u', $post->body, $matches);
            if (!empty($matches[1])) {
                $mentions = array_unique($matches[1]);
                foreach ($mentions as $usernameSlug) {
                    $user = \App\Models\User::whereRaw("LOWER(REPLACE(name, ' ', '-')) = ?", [strtolower($usernameSlug)])->first();
                    if ($user && $user->id !== Auth::id()) {
                        // DB notification
                        $user->notify(new \App\Notifications\UserMentionedNotification(Auth::user(), $post));

                        // Broadcast event for real-time notifications
                        event(new \App\Events\NotificationSent($user, [
                            'message' => 'mentioned you in a post',
                            'performer_name' => Auth::user()->name,
                            'performer_avatar' => Auth::user()->profile?->avatar_url ?? asset('images/default-avatar.png'),
                        ]));
                    }
                }
            }
        }

        return back()->with('success', 'Post published!');
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        if ($post->image) {
            Storage::disk('public')->delete('posts/' . $post->image);
        }

        $post->delete();

        return back()->with('success', 'Post deleted.');
    }
    public function share(Request $request, Post $post)
    {
        $request->validate([
            'body' => 'nullable|string|max:2000',
        ]);

        // If sharing a shared post, share the original
        $originalPostId = $post->shared_post_id ?? $post->id;

        Auth::user()->posts()->create([
            'body' => $request->body,
            'shared_post_id' => $originalPostId,
        ]);

        return back()->with('success', 'Post shared!');
    }
}
