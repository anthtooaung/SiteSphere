<?php

namespace App\Models;

use Database\Factories\TagsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tags extends Model
{
    /** @use HasFactory<TagsFactory> */
    use HasFactory;

    protected $guarded = [];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Categories::class, 'category_tags', 'tag_id', 'category_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Posts::class, 'post_tags', 'tag_id', 'post_id')
            ->withTimestamps();
    }
}
