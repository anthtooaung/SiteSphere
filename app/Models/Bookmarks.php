<?php

namespace App\Models;

use Database\Factories\BookmarksFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmarks extends Model
{
    /** @use HasFactory<BookmarksFactory> */
    use HasFactory;

    protected $guarded = [];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
