<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class)->latest();
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function activeStories()
    {
        return $this->hasMany(Story::class)->active()->latest();
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function isFollowing(User $user): bool
    {
        if ($this->relationLoaded('following')) {
            return $this->following->contains('following_id', $user->id);
        }
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Use the slugified name as the URL key (e.g. "Ali Binambo" → "ali-binambo").
     */
    public function getRouteKeyName(): string
    {
        return 'name';
    }

    /**
     * Return the slug value when generating route URLs.
     */
    public function getRouteKey(): mixed
    {
        return Str::slug($this->name);
    }

    /**
     * Resolve the model from the slug in the URL.
     * Loads all users and matches against their slug to avoid a LIKE query.
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        if (is_numeric($value)) {
            $user = self::find($value);
            if ($user) {
                return $user;
            }
        }

        $user = self::whereRaw("LOWER(REPLACE(name, ' ', '-')) = ?", [$value])->first();
        if ($user) {
            return $user;
        }

        $matchingUser = self::select('id', 'name')->get()->first(fn ($u) => Str::slug($u->name) === $value);
        return $matchingUser ? self::find($matchingUser->id) : null;
    }

    /**
     * Get the avatar URL — served through the controller from private storage.
     * Cache-busted by the profile's updated_at timestamp.
     */
    public function getAvatarUrlAttribute(): string
    {
        $v = $this->profile?->updated_at?->timestamp ?? 0;
        return route('profile.avatar', $this) . '?v=' . $v;
    }
}
