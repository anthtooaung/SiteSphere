<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FontsSeeder;
use Database\Seeders\ThemesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppearancePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('appearance'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_appearance_page_controls(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('appearance'));

        $response
            ->assertOk()
            ->assertSee('data-appearance-page', false)
            ->assertSee('Appearance Settings')
            ->assertSee('Theme Mode')
            ->assertSee('Default Purple')
            ->assertSee('Crimson Red')
            ->assertSee('Golden Yellow')
            ->assertSee('Emerald Green')
            ->assertSee('data-appearance-custom-toggle', false)
            ->assertSee('data-appearance-stable-panel', false)
            ->assertSee('x-transition.opacity.duration.160ms', false)
            ->assertSee('data-appearance-fonts', false)
            ->assertSee('data-appearance-font-search', false)
            ->assertSee('data-appearance-font-select', false)
            ->assertSee('data-appearance-font-preview', false)
            ->assertSee('class="font-search"', false)
            ->assertSee('class="font-select"', false)
            ->assertSee('class="font-preview-box"', false)
            ->assertSee('class="appearance-actions"', false)
            ->assertSee('class="save-btn"', false)
            ->assertSee('data-appearance-menu-layouts', false)
            ->assertSee('data-appearance-toast-positions', false)
            ->assertDontSee('appearance-font-option', false);
    }

    public function test_viewing_appearance_page_does_not_persist_unsaved_changes(): void
    {
        $user = User::factory()->create();
        $settingsBefore = DB::table('settings')
            ->where('user_id', $user->id)
            ->first();
        $fontIdBefore = DB::table('user_current_fonts')
            ->where('user_id', $user->id)
            ->value('font_id');

        $this->actingAs($user)
            ->get(route('appearance', [
                'dark_mode' => '1',
                'menuBar_location' => 'top',
                'noti_location' => 'bottom-end',
            ]))
            ->assertOk();

        $settingsAfter = DB::table('settings')
            ->where('user_id', $user->id)
            ->first();
        $fontIdAfter = DB::table('user_current_fonts')
            ->where('user_id', $user->id)
            ->value('font_id');

        $this->assertSame((bool) $settingsBefore->dark_mode, (bool) $settingsAfter->dark_mode);
        $this->assertSame($settingsBefore->menuBar_location, $settingsAfter->menuBar_location);
        $this->assertSame($settingsBefore->noti_location, $settingsAfter->noti_location);
        $this->assertSame((int) $fontIdBefore, (int) $fontIdAfter);
    }

    public function test_saving_preset_theme_updates_settings_and_clears_custom_theme(): void
    {
        $this->seed(ThemesSeeder::class);
        $user = User::factory()->create();
        $customThemeId = DB::table('custom_themes')->insertGetId([
            'user_id' => $user->id,
            'background_color' => '#111111',
            'text_color' => '#eeeeee',
            'accent_color' => '#123456',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('settings')->where('user_id', $user->id)->update(['custom_theme_id' => $customThemeId]);

        $themeId = DB::table('themes')->where('accent_color', '#DC2626')->value('id');
        $fontId = DB::table('fonts')->where('display_name', 'Inter')->value('id');

        $this->actingAs($user)
            ->patch(route('appearance.update'), [
                'dark_mode' => '0',
                'use_custom_theme' => '0',
                'theme_id' => $themeId,
                'font_id' => $fontId,
                'menuBar_location' => 'right',
                'noti_location' => 'bottom-start',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Appearance settings saved.');

        $this->assertDatabaseHas('settings', [
            'user_id' => $user->id,
            'theme_id' => $themeId,
            'custom_theme_id' => $customThemeId,
            'use_custom_theme' => false,
            'dark_mode' => false,
            'menuBar_location' => 'right',
            'noti_location' => 'bottom-start',
        ]);
        $this->assertDatabaseHas('user_current_fonts', [
            'user_id' => $user->id,
            'font_id' => $fontId,
        ]);
        $this->assertSame(1, DB::table('user_current_fonts')->where('user_id', $user->id)->count());

        $this->assertDatabaseHas('custom_themes', [
            'user_id' => $user->id,
            'background_color' => '#111111',
            'text_color' => '#eeeeee',
            'accent_color' => '#123456',
        ]);

        $this->actingAs($user)
            ->get(route('appearance'))
            ->assertOk()
            ->assertSee('value="#111111"', false)
            ->assertSee('value="#eeeeee"', false)
            ->assertSee('value="#123456"', false);
    }

    public function test_saving_custom_theme_stores_custom_colors_and_links_settings(): void
    {
        $user = User::factory()->create();
        $fontId = DB::table('fonts')->where('display_name', 'Poppins')->value('id');

        $this->actingAs($user)
            ->patch(route('appearance.update'), [
                'dark_mode' => '1',
                'use_custom_theme' => '1',
                'background_color' => '#101828',
                'text_color' => '#f8fafc',
                'accent_color' => '#14b8a6',
                'font_id' => $fontId,
                'menuBar_location' => 'top',
                'noti_location' => 'top-start',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Appearance settings saved.');

        $customThemeId = DB::table('custom_themes')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($customThemeId);
        $this->assertDatabaseHas('custom_themes', [
            'id' => $customThemeId,
            'user_id' => $user->id,
            'background_color' => '#101828',
            'text_color' => '#f8fafc',
            'accent_color' => '#14b8a6',
        ]);
        $this->assertDatabaseHas('settings', [
            'user_id' => $user->id,
            'custom_theme_id' => $customThemeId,
            'use_custom_theme' => true,
            'dark_mode' => true,
            'menuBar_location' => 'top',
            'noti_location' => 'top-start',
        ]);
        $this->assertDatabaseHas('user_current_fonts', [
            'user_id' => $user->id,
            'font_id' => $fontId,
        ]);
    }

    public function test_invalid_appearance_values_are_rejected(): void
    {
        $user = User::factory()->create();
        $fontId = DB::table('fonts')->where('display_name', 'Figtree')->value('id');

        $this->actingAs($user)
            ->from(route('appearance'))
            ->patch(route('appearance.update'), [
                'dark_mode' => '1',
                'use_custom_theme' => '1',
                'background_color' => 'not-a-color',
                'text_color' => '#ffffff',
                'accent_color' => '#6c5ce7',
                'font_id' => $fontId,
                'menuBar_location' => 'floating',
                'noti_location' => 'middle',
            ])
            ->assertRedirect(route('appearance'))
            ->assertSessionHasErrors(['background_color', 'menuBar_location', 'noti_location']);
    }

    public function test_saving_preset_theme_via_ajax_returns_json_response(): void
    {
        $this->seed(ThemesSeeder::class);
        $user = User::factory()->create();

        $themeId = DB::table('themes')->where('accent_color', '#DC2626')->value('id');
        $fontId = DB::table('fonts')->where('display_name', 'Inter')->value('id');

        $response = $this->actingAs($user)
            ->patchJson(route('appearance.update'), [
                'dark_mode' => '0',
                'use_custom_theme' => '0',
                'theme_id' => $themeId,
                'font_id' => $fontId,
                'menuBar_location' => 'right',
                'noti_location' => 'bottom-start',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Appearance settings saved.',
            ]);

        $this->assertDatabaseHas('settings', [
            'user_id' => $user->id,
            'theme_id' => $themeId,
            'use_custom_theme' => false,
            'dark_mode' => false,
            'menuBar_location' => 'right',
            'noti_location' => 'bottom-start',
        ]);
    }
}
