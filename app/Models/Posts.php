<?php

namespace App\Models;

use Database\Factories\PostsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Posts extends Model
{
    /** @use HasFactory<PostsFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_unsecure' => 'boolean',
        ];
    }

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

    public function reports(): HasMany
    {
        return $this->hasMany(Reports::class, 'target_id')
            ->where('target_name', 'post');
    }
}
