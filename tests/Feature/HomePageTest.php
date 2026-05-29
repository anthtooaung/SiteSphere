<?php

namespace Tests\Feature;

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

    public function test_home_uses_database_theme_and_font_variables(): void
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
            ->assertSee('--background-color: #102030;', false)
            ->assertSee('--text-color: #f8fafc;', false)
            ->assertSee('--font-family: "Open Sans", sans-serif;', false);
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
