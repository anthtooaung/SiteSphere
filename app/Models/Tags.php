<?php

namespace App\Models;

use Database\Factories\TagsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tags extends Model
{
    /** @use HasFactory<TagsFactory> */
    use HasFactory;

    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Posts::class, 'post_tags', 'tag_id', 'post_id')
            ->withTimestamps();
    }
}
