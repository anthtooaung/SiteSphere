<?php

namespace Tests\Feature;

use Database\Seeders\ThemesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ThemesSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_themes_seeder_creates_the_requested_accent_colors(): void
    {
        $this->seed(ThemesSeeder::class);

        foreach (ThemesSeeder::ACCENT_COLORS as $accentColor) {
            $this->assertDatabaseHas('themes', [
                'accent_color' => $accentColor,
            ]);
        }
    }

    public function test_themes_seeder_is_idempotent(): void
    {
        $this->seed(ThemesSeeder::class);
        $this->seed(ThemesSeeder::class);

        foreach (ThemesSeeder::ACCENT_COLORS as $accentColor) {
            $this->assertSame(
                1,
                DB::table('themes')->where('accent_color', $accentColor)->count(),
            );
        }
    }
}
