<?php

namespace App\Models;

use Database\Factories\SettingsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    /** @use HasFactory<SettingsFactory> */
    use HasFactory;

    /**
     * Theme relation (default theme)
     */
    public function theme()
    {
        return $this->belongsTo(Themes::class);
    }

    /**
     * Custom theme relation (per-user custom theme)
     */
    public function customTheme()
    {
        return $this->belongsTo(CustomThemes::class, 'custom_theme_id');
    }
}
