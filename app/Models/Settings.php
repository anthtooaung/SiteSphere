<?php

namespace App\Models;

use Database\Factories\SettingsFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'menuBar_location', 'noti_location', 'dark_mode', 'user_post_visible', 'theme_id', 'custom_theme_id', 'use_custom_theme'])]
class Settings extends Model
{
    /** @use HasFactory<SettingsFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dark_mode' => 'boolean',
            'user_post_visible' => 'boolean',
            'use_custom_theme' => 'boolean',
        ];
    }

    /**
     * Theme relation (default theme)
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Themes::class);
    }

    /**
     * Custom theme relation (per-user custom theme)
     */
    public function customTheme(): BelongsTo
    {
        return $this->belongsTo(CustomThemes::class, 'custom_theme_id');
    }
}
