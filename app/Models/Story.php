<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image',
        'caption',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include stories created within the last 24 hours.
     */
    public function scopeActive($query)
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }

    /**
     * Get the story's image URL.
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/stories/' . $this->image);
    }
}
