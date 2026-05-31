<?php

namespace App\Models;

use Database\Factories\CategoriesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Categories extends Model
{
    /** @use HasFactory<CategoriesFactory> */
    use HasFactory;

    protected $guarded = [];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tags::class, 'category_tags', 'category_id', 'tag_id');
    }
}
