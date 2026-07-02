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
}
