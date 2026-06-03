<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'background_color', 'text_color', 'accent_color'])]
class CustomThemes extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
