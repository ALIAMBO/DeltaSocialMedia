<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'avatar',
        'cover_photo',
        'bio',
        'location',
        'website',
        'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * URL to stream the avatar. Includes updated_at as cache-buster
     * so the browser re-fetches after every profile save.
     */
    public function getAvatarUrlAttribute(): string
    {
        return route('profile.avatar', $this->user_id)
             . '?v=' . ($this->updated_at?->timestamp ?? 0);
    }

    /**
     * URL to stream the cover photo. Includes updated_at as cache-buster.
     */
    public function getCoverUrlAttribute(): string
    {
        return route('profile.cover', $this->user_id)
             . '?v=' . ($this->updated_at?->timestamp ?? 0);
    }
}
