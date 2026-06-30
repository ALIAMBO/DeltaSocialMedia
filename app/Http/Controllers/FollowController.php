<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(User $user)
    {
        $authUser = Auth::user();

        if ($authUser->id === $user->id) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        $follow = $authUser->following()->where('following_id', $user->id)->first();

        if ($follow) {
            $follow->delete();
            $message = 'Unfollowed ' . $user->name;
        } else {
            $authUser->following()->create(['following_id' => $user->id]);
            $user->notify(new \App\Notifications\NewFollowNotification($authUser));
            $message = 'Now following ' . $user->name;
        }

        return back()->with('success', $message);
    }
}
