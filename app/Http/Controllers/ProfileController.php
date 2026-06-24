<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->load(['profile', 'posts.likes', 'posts.comments.user', 'followers', 'following']);
        $isFollowing = Auth::check() && Auth::user()->isFollowing($user);
        return view('profile.show', compact('user', 'isFollowing'));
    }

    public function edit()
    {
        $user = Auth::user()->load('profile');
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'bio'         => 'nullable|string|max:500',
            'location'    => 'nullable|string|max:100',
            'website'     => 'nullable|string|max:255',
            'birth_date'  => 'nullable|date',
            'avatar'      => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'cover_photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $authUser = Auth::user();
        $authUser->name = $request->name;
        $authUser->save();

        $profile = $authUser->profile ?? $authUser->profile()->create(['avatar' => null]);

        $profile->bio        = $request->bio;
        $profile->location   = $request->location;
        $profile->website    = $request->filled('website') ? $request->website : null;
        $profile->birth_date = $request->birth_date ?: null;

        // ── Avatar ──────────────────────────────────────────────────────────
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Delete old file (skip legacy default-avatar.png)
            if ($profile->avatar && $profile->avatar !== 'default-avatar.png') {
                Storage::disk('local')->delete('avatars/' . $profile->avatar);
            }
            $ext      = $request->file('avatar')->getClientOriginalExtension();
            $filename = uniqid('avatar_', true) . '.' . $ext;
            $request->file('avatar')->storeAs('avatars', $filename, 'local');
            $profile->avatar = $filename;
        }

        // ── Cover photo ─────────────────────────────────────────────────────
        if ($request->hasFile('cover_photo') && $request->file('cover_photo')->isValid()) {
            if ($profile->cover_photo) {
                Storage::disk('local')->delete('covers/' . $profile->cover_photo);
            }
            $ext      = $request->file('cover_photo')->getClientOriginalExtension();
            $filename = uniqid('cover_', true) . '.' . $ext;
            $request->file('cover_photo')->storeAs('covers', $filename, 'local');
            $profile->cover_photo = $filename;
        }

        $profile->save();

        return redirect()->route('profile.show', $authUser)->with('success', 'Profile updated successfully!');
    }

    /**
     * Stream avatar from private storage.
     * Public route — no auth middleware so <img> tags can fetch it.
     */
    public function avatar(User $user)
    {
        $filename = $user->profile?->avatar;
        $path     = 'avatars/' . $filename;

        $hasRealFile = $filename
            && $filename !== 'default-avatar.png'
            && Storage::disk('local')->exists($path);

        if (!$hasRealFile) {
            return $this->svgAvatar($user->name);
        }

        $fullPath = Storage::disk('local')->path($path);
        $mime     = $this->detectMime($fullPath);
        $updated  = Storage::disk('local')->lastModified($path);

        return response()->file($fullPath, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $updated) . ' GMT',
        ]);
    }

    /**
     * Stream cover photo from private storage.
     * Public route — no auth middleware so <img> tags can fetch it.
     */
    public function cover(User $user)
    {
        $filename = $user->profile?->cover_photo;
        $path     = 'covers/' . $filename;

        if (!$filename || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($path);
        $mime     = $this->detectMime($fullPath);
        $updated  = Storage::disk('local')->lastModified($path);

        return response()->file($fullPath, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $updated) . ' GMT',
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function detectMime(string $fullPath): string
    {
        // finfo is the most reliable cross-platform MIME detector
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $fullPath);
            finfo_close($finfo);
            if ($mime) {
                return $mime;
            }
        }

        // Fallback: derive from extension
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            default       => 'application/octet-stream',
        };
    }

    private function svgAvatar(string $name): \Illuminate\Http\Response
    {
        $letter = strtoupper(mb_substr($name, 0, 1)) ?: '?';
        $colors = ['4F46E5', '0891B2', '059669', 'D97706', 'DC2626', '7C3AED', 'DB2777', 'EA580C'];
        // abs() ensures positive index on 64-bit systems where crc32 can be negative
        $color  = $colors[abs(crc32($name)) % count($colors)];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
             . '<circle cx="50" cy="50" r="50" fill="#' . $color . '"/>'
             . '<text x="50" y="50" dy=".35em" text-anchor="middle" '
             . 'font-family="Arial,sans-serif" font-size="44" font-weight="bold" fill="white">'
             . htmlspecialchars($letter)
             . '</text></svg>';

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }
}
