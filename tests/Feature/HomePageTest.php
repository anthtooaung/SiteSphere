<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_from_home_to_login(): void
    {
        $response = $this->get('/home');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_empty_home_without_review_cards(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('Discover Useful Websites')
            ->assertSee('<strong id="resultsCount">0</strong>', false)
            ->assertDontSee('Aurora Pay checkout keeps timing out')
            ->assertDontSee('class="review-card', false)
            ->assertDontSee('cdnjs.cloudflare.com/ajax/libs/font-awesome', false)
            ->assertDontSee('class="fas', false)
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('class="desktop-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_home_hides_show_more_categories_when_there_are_five_or_fewer_categories(): void
    {
        $user = User::factory()->create();

        Categories::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertDontSee('id="showCategoryBtn"', false)
            ->assertDontSee('Show More Categories');
    }

    public function test_home_shows_show_more_categories_when_there_are_more_than_five_categories(): void
    {
        $user = User::factory()->create();

        Categories::factory()->count(6)->create();

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('id="showCategoryBtn"', false)
            ->assertSee('Show More Categories');
    }

    public function test_home_uses_light_mode_theme_and_font_variables(): void
    {
        $user = User::factory()->create();
        $fontId = DB::table('fonts')
            ->where('font_family', '"Open Sans", sans-serif')
            ->value('id');
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

        DB::table('user_current_fonts')->insert([
            'user_id' => $user->id,
            'font_id' => $fontId,
            'created_at' => now()->addSecond(),
            'updated_at' => now()->addSecond(),
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('--accent-color: #14b8a6;', false)
            ->assertSee('--background-color: #ffffff;', false)
            ->assertSee('--text-color: #0d1b2a;', false)
            ->assertSee('--font-family: "Open Sans", sans-serif;', false);
    }

    public function test_home_uses_dark_mode_variables_without_changing_accent(): void
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
                'dark_mode' => true,
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('--accent-color: #14b8a6;', false)
            ->assertSee('--background-color: #000000;', false)
            ->assertSee('--text-color: #ffffff;', false);
    }

    public function test_home_renders_aside_component_with_default_placement(): void
    {
        $user = User::factory()->create();

        DB::table('settings')
            ->where('user_id', $user->id)
            ->update([
                'menuBar_location' => 'left',
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('id="sidebarToggle"', false)
            ->assertSee('id="sidebar"', false)
            ->assertSee('home-aside--left', false)
            ->assertSee('data-menu-bar-location="left"', false);
    }

    public function test_home_aside_uses_each_valid_menu_bar_location(): void
    {
        foreach (['left', 'right', 'top', 'bottom'] as $location) {
            $user = User::factory()->create();

            DB::table('settings')
                ->where('user_id', $user->id)
                ->update([
                    'menuBar_location' => $location,
                    'updated_at' => now(),
                ]);

            $response = $this->actingAs($user)->get('/home');

            $response
                ->assertOk()
                ->assertSee('home-aside--'.$location, false)
                ->assertSee('data-menu-bar-location="'.$location.'"', false);

            if (in_array($location, ['top', 'bottom'], true)) {
                $response
                    ->assertSee('home-aside--dropdown', false)
                    ->assertSee('data-dropdown-aside="true"', false)
                    ->assertSee('home-aside-header-primary', false)
                    ->assertSee('Refine Website')
                    ->assertSee('home-aside-header-secondary', false)
                    ->assertSee('by rating, category, and tags.');
            } else {
                $response
                    ->assertDontSee('home-aside--dropdown', false)
                    ->assertSee('data-dropdown-aside="false"', false)
                    ->assertSee('Refine websites by rating, category, and tags.')
                    ->assertDontSee('home-aside-header-primary', false)
                    ->assertDontSee('home-aside-header-secondary', false);
            }
        }
    }

    public function test_home_aside_falls_back_to_left_for_invalid_or_missing_settings(): void
    {
        $this->blade('<x-layout.home-aside :categories="collect()" menu-bar-location="sideways" />')
            ->assertSee('home-aside--left', false)
            ->assertSee('data-menu-bar-location="left"', false);

        $missingSettingsUser = User::factory()->create();

        DB::table('settings')
            ->where('user_id', $missingSettingsUser->id)
            ->delete();

        $this->actingAs($missingSettingsUser)
            ->get('/home')
            ->assertOk()
            ->assertSee('home-aside--left', false)
            ->assertSee('data-menu-bar-location="left"', false);
    }

    public function test_home_renders_one_card_per_post_with_many_descriptions(): void
    {
        $viewer = User::factory()->create();
        $firstReviewer = User::factory()->create(['name' => 'First Reviewer']);
        $secondReviewer = User::factory()->create(['name' => 'Second Reviewer']);
        $post = Posts::factory()->create([
            'title' => 'Shared URL Review',
            'url' => 'https://shared-example.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $firstReviewer->id,
            'description' => 'First description for the same website.',
        ]);
        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $secondReviewer->id,
            'description' => 'Second description for the same website.',
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response
            ->assertOk()
            ->assertSee('Shared URL Review')
            ->assertSee('https://shared-example.test')
            ->assertSee('First description for the same website.')
            ->assertSee('Second description for the same website.');

        $this->assertSame(1, substr_count($response->getContent(), 'class="review-card'));
    }

    public function test_comments_and_ratings_are_post_level_totals(): void
    {
        $viewer = User::factory()->create();
        $post = Posts::factory()->create([
            'title' => 'Post Level Totals',
            'url' => 'https://totals-example.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'description' => 'Description does not own comments or ratings.',
        ]);

        Comments::factory()->count(2)->create([
            'post_id' => $post->id,
        ]);
        Ratings::factory()->create([
            'post_id' => $post->id,
            'rating' => 5,
        ]);
        Ratings::factory()->create([
            'post_id' => $post->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response
            ->assertOk()
            ->assertSee('Post Level Totals')
            ->assertSee('x-text="commentsTotal()">2</span>', false)
            ->assertSee('<span x-text="ratingsTotal()">2</span> ratings', false)
            ->assertSee('>4.0</span>', false);
    }
}
