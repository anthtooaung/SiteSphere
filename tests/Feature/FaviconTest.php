<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FaviconTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_layout_uses_svg_favicon(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('<link rel="icon" type="image/svg+xml" href="/favicon.svg">', false);
    }

    public function test_dashboard_layout_uses_svg_favicon(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('<link rel="icon" type="image/svg+xml" href="/favicon.svg">', false);
    }

    public function test_guest_favicon_uses_default_accent_color_even_when_themes_exist(): void
    {
        DB::table('themes')->insert([
            'accent_color' => '#059669',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/favicon.svg');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee('<svg', false)
            ->assertSee('fill="#6c5ce7"', false)
            ->assertDontSee('fill="#059669"', false);
    }

    public function test_authenticated_favicon_uses_user_custom_accent_color(): void
    {
        $user = User::factory()->create();
        $customThemeId = DB::table('custom_themes')->insertGetId([
            'user_id' => $user->id,
            'background_color' => '#102030',
            'text_color' => '#f8fafc',
            'accent_color' => '#14b8a6',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')
            ->where('user_id', $user->id)
            ->update([
                'custom_theme_id' => $customThemeId,
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($user)->get('/favicon.svg');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee('<svg', false)
            ->assertSee('fill="#14b8a6"', false);
    }

    public function test_favicon_falls_back_when_database_accent_color_is_invalid(): void
    {
        $user = User::factory()->create();
        $customThemeId = DB::table('custom_themes')->insertGetId([
            'user_id' => $user->id,
            'background_color' => '#102030',
            'text_color' => '#f8fafc',
            'accent_color' => 'not-a-color',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')
            ->where('user_id', $user->id)
            ->update([
                'custom_theme_id' => $customThemeId,
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($user)->get('/favicon.svg');

        $response
            ->assertOk()
            ->assertSee('fill="#6c5ce7"', false)
            ->assertDontSee('not-a-color', false);
    }
}
