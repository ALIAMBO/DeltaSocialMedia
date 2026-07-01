<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image'   => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'caption' => 'nullable|string|max:150',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('stories', 'public');
            $imageName = basename($imagePath);
        }

        Auth::user()->stories()->create([
            'image'   => $imageName,
            'caption' => $request->caption,
        ]);

        return back()->with('success', 'Story uploaded successfully!');
    }

    public function destroy(Story $story)
    {
        if ($story->user_id !== Auth::id()) {
            abort(403);
        }

        if ($story->image) {
            Storage::disk('public')->delete('stories/' . $story->image);
        }

        $story->delete();

        return back()->with('success', 'Story deleted.');
    }
}
