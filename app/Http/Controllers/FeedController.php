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

        $posts = Post::with(['user.profile', 'likes', 'comments.user.profile'])
            ->whereIn('user_id', $feedIds)
            ->latest()
            ->paginate(15);

        // Eager load following on authenticated user to avoid N+1 query in templates/widgets
        $user->load('following');

        // Fetch users (self + followed users) with active stories (last 24 hours)
        $usersWithStories = \App\Models\User::whereIn('id', $feedIds)
            ->whereHas('stories', function ($query) {
                $query->where('created_at', '>=', now()->subHours(24));
            })
            ->with(['stories' => function ($query) {
                $query->where('created_at', '>=', now()->subHours(24))->oldest();
            }, 'profile'])
            ->get()
            ->sortByDesc(function ($u) {
                return $u->stories->last()?->created_at;
            })
            ->values();

        // Fetch suggested users (who to follow)
        $suggestions = \App\Models\User::where('id', '!=', $user->id)
            ->whereNotIn('id', $followingIds)
            ->with('profile')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('feed.index', compact('posts', 'usersWithStories', 'suggestions'));
    }
}
