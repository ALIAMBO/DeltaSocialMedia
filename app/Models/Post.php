<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'body',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function isLikedBy(User $user): bool
    {
        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $user->id);
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/posts/' . $this->image) : null;
    }

    public function getFormattedBodyAttribute(): string
    {
        if (!$this->body) {
            return '';
        }

        $body = e($this->body);

        // Parse hashtags (#tagname)
        // Matches # followed by word characters or unicode word characters
        $body = preg_replace_callback('/#([\w\x{4e00}-\x{9fa5}]+)/u', function ($matches) {
            $tag = $matches[1];
            $url = route('feed', ['tag' => $tag]);
            return '<a href="' . $url . '" class="text-green-600 dark:text-green-400 font-semibold hover:underline">#' . htmlspecialchars($tag) . '</a>';
        }, $body);

        // Parse mentions (@username-slug)
        // Matches @ followed by word characters, dashes, or underscores
        $body = preg_replace_callback('/@([\w\-]+)/u', function ($matches) {
            $usernameSlug = $matches[1];
            
            // Cached lookup to prevent N+1 queries when loading multiple posts
            $userData = \Illuminate\Support\Facades\Cache::remember("user_mention_{$usernameSlug}", 300, function () use ($usernameSlug) {
                $user = \App\Models\User::whereRaw("LOWER(REPLACE(name, ' ', '-')) = ?", [strtolower($usernameSlug)])->first();
                return $user ? ['slug' => \Illuminate\Support\Str::slug($user->name)] : null;
            });

            if ($userData) {
                $url = route('profile.show', ['user' => $userData['slug']]);
                return '<a href="' . $url . '" class="text-green-600 dark:text-green-400 font-semibold hover:underline">@' . htmlspecialchars($matches[1]) . '</a>';
            }

            return $matches[0];
        }, $body);

        return $body;
    }
}
