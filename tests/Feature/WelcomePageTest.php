<?php

namespace Tests\Feature;

use App\Models\Posts;
use App\Models\Ratings;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    private const MOBILE_USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    private function getAsAuthenticatedMobileUser(User $user, string $uri): TestResponse
    {
        $previousUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $_SERVER['HTTP_USER_AGENT'] = self::MOBILE_USER_AGENT;

        try {
            return $this->actingAs($user)->get($uri);
        } finally {
            if ($previousUserAgent === null) {
                unset($_SERVER['HTTP_USER_AGENT']);
            } else {
                $_SERVER['HTTP_USER_AGENT'] = $previousUserAgent;
            }
        }
    }

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
        $response->assertSee('welcome-search-bar', false);
        $response->assertSee('id="welcomeSearch"', false);
        $response->assertSee('type="search"', false);
        $response->assertSee('mailto:anthtooaung2792005@outlook.com', false);
        $response->assertSee('https://github.com/anthtooaung', false);
        $response->assertSee('https://www.linkedin.com/in/ant-htoo-aung-460006395', false);
        $response->assertSeeText('Most Reviewed Websites');
        $response->assertSeeText('No reviewed websites yet');
        $response->assertSeeText('Get in touch');
    }

    public function test_welcome_page_renders_most_reviewed_websites_from_database(): void
    {
        $firstReviewer = User::factory()->create();
        $secondReviewer = User::factory()->create();
        $topPost = Posts::factory()->create([
            'title' => 'Laravel Boost Hub',
            'url' => 'https://boost.example.test',
        ]);
        $secondPost = Posts::factory()->create([
            'title' => 'Query Scout',
            'url' => 'https://query-scout.example.test',
        ]);
        $hiddenOnlyPost = Posts::factory()->create([
            'title' => 'Hidden Placeholder',
            'url' => 'https://hidden.example.test',
        ]);

        UserPosts::factory()->count(3)->create([
            'post_id' => $topPost->id,
            'user_hidden' => false,
        ]);
        UserPosts::factory()->create([
            'post_id' => $secondPost->id,
            'user_hidden' => false,
        ]);
        UserPosts::factory()->create([
            'post_id' => $hiddenOnlyPost->id,
            'user_hidden' => true,
        ]);

        Ratings::factory()->create([
            'user_id' => $firstReviewer->id,
            'post_id' => $topPost->id,
            'rating' => 5,
        ]);
        Ratings::factory()->create([
            'user_id' => $secondReviewer->id,
            'post_id' => $topPost->id,
            'rating' => 4,
        ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSeeText('Laravel Boost Hub')
            ->assertSeeText('boost.example.test')
            ->assertSeeText('Reviewed by 3 members')
            ->assertSeeText('average rating of 4.5')
            ->assertSeeText('Query Scout')
            ->assertDontSeeText('Hidden Placeholder')
            ->assertDontSeeText('Process Academy')
            ->assertDontSeeText('DesignFlow AI')
            ->assertDontSeeText('Lunaver Cloud');
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

    public function test_guest_welcome_page_uses_database_default_font_and_theme(): void
    {
        DB::table('themes')->truncate();
        DB::table('fonts')->truncate();

        DB::table('themes')->insert([
            'accent_color' => '#059669',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('fonts')->insert([
            'display_name' => 'Inter',
            'google_family' => 'Inter',
            'font_family' => '"Inter", sans-serif',
            'sort_order' => 1,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('--accent-color: #6c5ce7', false);
        $response->assertSee('--font-family: "Inter", sans-serif', false);
    }

    public function test_guest_welcome_page_renders_the_auth_nav_menu(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('id="dropdownDividerButton"', false);
        $response->assertSee('data-dropdown-toggle="dropdownDivider"', false);
        $response->assertSee('class="auth-menu-button"', false);
        $response->assertSee('class="auth-menu-dropdown hidden"', false);
        $response->assertSee('href="'.route('login').'"', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSeeText('Login / Register');
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
            'created_at' => now()->addMinute(),
            'updated_at' => now()->addMinute(),
        ]);

        $response = $this->getAsAuthenticatedMobileUser($user, '/');

        $response->assertOk();
        $response->assertSee('--font-family: "Open Sans", sans-serif', false);
        $response->assertSee('id="welcomeSearch"', false);
        $response->assertDontSee('id="mobileSearchForm"', false);
        $response->assertDontSee('Search reviews...', false);
    }

    public function test_authenticated_mobile_user_sees_nav_search_outside_welcome_page(): void
    {
        $user = User::factory()->create();

        $response = $this->getAsAuthenticatedMobileUser($user, '/home');

        $response->assertOk();
        $response->assertSee('id="mobileSearchForm"', false);
        $response->assertSee('Search reviews...', false);
    }

    public function test_authenticated_user_theme_color_is_applied_to_welcome_page(): void
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
                'use_custom_theme' => true,
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('--accent-color: #14b8a6', false);
    }
}
