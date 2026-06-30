<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    /**
     * Show the main feed: posts from people the auth user follows + own posts.
     */
    public function index()
    {
        $user = Auth::user();

        // IDs of users the current user follows
        $followingIds = $user->following()->pluck('following_id');
        $feedIds = $followingIds->push($user->id);

        $posts = Post::with(['user.profile', 'likes', 'comments.user'])
            ->whereIn('user_id', $feedIds)
            ->latest()
            ->paginate(15);

        // Fetch users (self + followed users) with active stories (last 24 hours)
        $usersWithStories = \App\Models\User::whereIn('id', $feedIds)
            ->whereHas('stories', function ($query) {
                $query->where('created_at', '>=', now()->subHours(24));
            })
            ->with(['stories' => function ($query) {
                $query->where('created_at', '>=', now()->subHours(24))->latest();
            }, 'profile'])
            ->get();

        return view('feed.index', compact('posts', 'usersWithStories'));
    }
}
