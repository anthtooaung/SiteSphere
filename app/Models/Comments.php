<?php

namespace App\Models;

use Database\Factories\CommentsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comments extends Model
{
    /** @use HasFactory<CommentsFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commentReactions(): HasMany
    {
        return $this->hasMany(CommentReactions::class, 'comment_id');
    }
}
