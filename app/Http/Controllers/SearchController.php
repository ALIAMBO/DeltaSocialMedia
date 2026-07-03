<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        
        if ($query && str_starts_with($query, '#')) {
            return redirect()->route('feed', ['tag' => substr($query, 1)]);
        }

        $users = collect();
        $currentUser = Auth::user();

        // Eager load following on the authenticated user to avoid N+1 queries in loop
        $currentUser->load('following');

        if ($query && strlen($query) >= 2) {
            $users = User::where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->where('id', '!=', $currentUser->id)
                ->with('profile')
                ->withCount(['posts', 'followers'])
                ->limit(20)
                ->get();
        }

        return view('search.results', [
            'query' => $query,
            'users' => $users,
        ]);
    }

    public function searchFollowing(Request $request)
    {
        $query = $request->input('q');
        $user = Auth::user();

        if (empty($query)) {
            return response()->json([]);
        }

        $followingIds = $user->following()->pluck('following_id');

        $users = User::whereIn('id', $followingIds)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->with('profile')
            ->limit(10)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'slug' => \Illuminate\Support\Str::slug($u->name),
                    'avatar' => $u->profile?->avatar_url ?? asset('images/default-avatar.png'),
                ];
            });

        return response()->json($users);
    }
}
