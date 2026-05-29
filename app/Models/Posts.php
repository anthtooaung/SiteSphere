<?php

namespace App\Models;

use Database\Factories\PostsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Posts extends Model
{
    /** @use HasFactory<PostsFactory> */
    use HasFactory;

    protected $guarded = [];

    public function userPosts(): HasMany
    {
        return $this->hasMany(UserPosts::class, 'post_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Ratings::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comments::class, 'post_id');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmarks::class, 'post_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tags::class, 'post_tags', 'post_id', 'tag_id')
            ->withTimestamps();
    }
}
