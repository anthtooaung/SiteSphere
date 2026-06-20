<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserDefaultPreferencesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_new_users_receive_default_settings_and_font(): void
    {
        $user = User::factory()->create();

        $themeId = DB::table('themes')
            ->where('accent_color', '#6c5ce7')
            ->value('id');

        $defaultFontId = DB::table('fonts')
            ->where('is_default', true)
            ->value('id');

        $this->assertNotNull($themeId);
        $this->assertNotNull($defaultFontId);

        $this->assertDatabaseHas('settings', [
            'user_id' => $user->id,
            'menuBar_location' => 'left',
            'noti_location' => 'top-end',
            'dark_mode' => false,
            'user_post_visible' => true,
            'theme_id' => $themeId,
        ]);

        $this->assertDatabaseHas('user_current_fonts', [
            'user_id' => $user->id,
            'font_id' => $defaultFontId,
        ]);
    }
}
