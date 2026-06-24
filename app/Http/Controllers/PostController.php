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

        Auth::user()->posts()->create([
            'body'  => $request->body,
            'image' => $imageName,
        ]);

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
}
