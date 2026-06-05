<?php

namespace App\Models;

use Database\Factories\CategoryTagsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTags extends Model
{
    /** @use HasFactory<CategoryTagsFactory> */
    use HasFactory;
}
