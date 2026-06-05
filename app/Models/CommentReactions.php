<?php

namespace App\Models;

use Database\Factories\CommentReactionsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReactions extends Model
{
    /** @use HasFactory<CommentReactionsFactory> */
    use HasFactory;

    protected $guarded = [];
}
