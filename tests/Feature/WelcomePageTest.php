<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_welcome_page_renders_new_main_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText("Don't drown in documentation");
        $response->assertSee('id="welcomeHeroTitle"', false);
        $response->assertSee('welcome-word-toggle', false);
        $response->assertSee('data-word-state="a">Less', false);
        $response->assertSee('data-word-state="b">More', false);
        $response->assertSee('welcome-word-action', false);
        $response->assertSee('data-word-state="a">Searching', false);
        $response->assertSee('data-word-state="b">Building', false);
        $response->assertSeeText('Most Reviewed Websites');
        $response->assertSeeText('Process Academy');
        $response->assertSeeText('Get in touch');
    }

    public function test_guest_welcome_page_includes_theme_and_font_variables(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('--accent-color: #6c5ce7', false);
        $response->assertSee('--background-color: #ffffff', false);
        $response->assertSee('--text-color: #0d1b2a', false);
        $response->assertSee('--font-family: Figtree, sans-serif', false);
    }

    public function test_authenticated_user_current_font_is_applied_to_layout(): void
    {
        $user = User::factory()->create();

        $fontId = DB::table('fonts')->insertGetId([
            'font_family' => '"Open Sans", sans-serif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_current_fonts')->insert([
            'user_id' => $user->id,
            'font_id' => $fontId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('--font-family: "Open Sans", sans-serif', false);
    }
}
