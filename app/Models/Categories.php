<?php

namespace App\Models;

use Database\Factories\CategoriesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categories extends Model
{
    /** @use HasFactory<CategoriesFactory> */
    use HasFactory;

    protected $guarded = [];

    public function tags(): HasMany
    {
        return $this->hasMany(Tags::class, 'category_id');
    }
}
