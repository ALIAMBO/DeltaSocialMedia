<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'body' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $request->body,
            'parent_id' => $request->parent_id,
        ]);

        if (!$request->parent_id && $post->user_id !== Auth::id()) {
            $post->user->notify(new \App\Notifications\NewCommentNotification(Auth::user(), $post, $comment));
            event(new \App\Events\NotificationSent($post->user, [
                'message' => 'commented on your post: "' . \Illuminate\Support\Str::limit($comment->body, 30) . '"',
                'performer_name' => Auth::user()->name,
                'performer_avatar' => Auth::user()->profile?->avatar_url ?? asset('images/default-avatar.png'),
            ]));
        }

        if ($request->parent_id) {
            $parent = \App\Models\Comment::find($request->parent_id);
            if ($parent && $parent->user_id !== Auth::id()) {
                $parent->user->notify(new \App\Notifications\UserRepliedToCommentNotification(Auth::user(), $parent, $comment));
                event(new \App\Events\NotificationSent($parent->user, [
                    'message' => 'replied to your comment: "' . \Illuminate\Support\Str::limit($comment->body, 30) . '"',
                    'performer_name' => Auth::user()->name,
                    'performer_avatar' => Auth::user()->profile?->avatar_url ?? asset('images/default-avatar.png'),
                ]));
            }
        }

        if ($request->wantsJson()) {
            $comment->load('user.profile');
            return response()->json([
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'user_name' => $comment->user->name,
                    'avatar_url' => $comment->user->profile?->avatar_url ?? asset('images/default-avatar.png'),
                    'profile_url' => route('profile.show', $comment->user),
                    'created_at_diff' => $comment->created_at->diffForHumans(),
                    'delete_url' => route('comments.destroy', $comment),
                    'parent_id' => $comment->parent_id,
                ],
                'comments_count' => $post->comments()->count(),
            ]);
        }

        return back();
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $postId = $comment->post_id;
        $comment->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'comments_count' => \App\Models\Comment::where('post_id', $postId)->count(),
            ]);
        }

        return back();
    }
}
