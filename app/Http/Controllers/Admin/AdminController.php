<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── Dashboard ──────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'users'    => User::count(),
            'posts'    => Post::count(),
            'stories'  => Story::count(),
            'banned'   => User::onlyTrashed()->count(),
            'admins'   => User::where('is_admin', true)->count(),
            'new_this_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentPosts = Post::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentPosts'));
    }

    // ── User Management ────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::withTrashed()->withCount('posts')
            ->when($request->search, fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            );

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function banUser(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Cannot ban an admin.');
        }
        $user->delete(); // soft delete
        return back()->with('success', "{$user->name} has been banned.");
    }

    public function unbanUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return back()->with('success', "{$user->name} has been unbanned.");
    }

    public function promoteUser(User $user)
    {
        $user->update(['is_admin' => true]);
        return back()->with('success', "{$user->name} is now an admin.");
    }

    public function demoteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot demote yourself.');
        }
        $user->update(['is_admin' => false]);
        return back()->with('success', "{$user->name} has been demoted.");
    }

    public function deleteUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->forceDelete();
        return back()->with('success', 'User permanently deleted.');
    }

    // ── Post Management ────────────────────────────────────────────────────────

    public function posts(Request $request)
    {
        $query = Post::with(['user', 'likes', 'comments'])
            ->when($request->search, fn ($q) =>
                $q->where('body', 'like', "%{$request->search}%")
            );

        $posts = $query->latest()->paginate(20)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function deletePost(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }

    // ── Story Management ───────────────────────────────────────────────────────

    public function stories(Request $request)
    {
        $stories = Story::with('user')->latest()->paginate(20);
        return view('admin.stories.index', compact('stories'));
    }

    public function deleteStory(Story $story)
    {
        $story->delete();
        return back()->with('success', 'Story deleted.');
    }
}
