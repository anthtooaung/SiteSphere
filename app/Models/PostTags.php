<?php

namespace App\Models;

use Database\Factories\PostTagsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTags extends Model
{
    /** @use HasFactory<PostTagsFactory> */
    use HasFactory;
}
