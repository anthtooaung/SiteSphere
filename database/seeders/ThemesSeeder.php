<?php

namespace Database\Seeders;

use App\Models\Themes;
use Illuminate\Database\Seeder;

class ThemesSeeder extends Seeder
{
    public const ACCENT_COLORS = [
        '#DC2626',
        '#f4c543',
        '#059669',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ACCENT_COLORS as $accentColor) {
            Themes::query()->firstOrCreate([
                'accent_color' => $accentColor,
            ]);
        }
    }
}
