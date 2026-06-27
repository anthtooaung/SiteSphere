<?php

namespace Tests\Feature;

use App\Models\Posts;
use App\Models\Ratings;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Database\Seeders\ThemesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
        $this->seed(ThemesSeeder::class);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile-detail', $user->slug));

        $response->assertOk();
    }

    public function test_profile_recent_review_ratings_are_loaded_without_per_card_queries(): void
    {
        $user = User::factory()->create();

        for ($index = 1; $index <= 4; $index++) {
            $post = Posts::factory()->create([
                'title' => 'Profile Review '.$index,
            ]);

            UserPosts::factory()->create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'description' => 'Profile review description '.$index,
            ]);

            Ratings::factory()->create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'rating' => $index,
            ]);
        }

        $ratingQueries = 0;
        DB::listen(function ($query) use (&$ratingQueries): void {
            if (str_contains($query->sql, 'from "ratings"') || str_contains($query->sql, 'from `ratings`')) {
                $ratingQueries++;
            }
        });

        $response = $this
            ->actingAs($user)
            ->get(route('profile-detail', $user->slug));

        $response
            ->assertOk()
            ->assertSee('Profile Review 1')
            ->assertSee('Profile Review 4')
            ->assertSee('★ 1.0', false)
            ->assertSee('★ 4.0', false);

        $this->assertLessThanOrEqual(4, $ratingQueries);
    }

    public function test_profile_hides_hidden_reviews_from_other_users(): void
    {
        $profileOwner = User::factory()->create();
        $viewer = User::factory()->create();
        $visiblePost = Posts::factory()->create(['title' => 'Visible Profile Review']);
        $hiddenPost = Posts::factory()->create(['title' => 'Hidden Profile Review']);

        UserPosts::factory()->create([
            'user_id' => $profileOwner->id,
            'post_id' => $visiblePost->id,
            'description' => 'Visible profile review description.',
            'user_hidden' => false,
        ]);
        UserPosts::factory()->create([
            'user_id' => $profileOwner->id,
            'post_id' => $hiddenPost->id,
            'description' => 'Hidden profile review description.',
            'user_hidden' => true,
        ]);

        $response = $this
            ->actingAs($viewer)
            ->get(route('profile-detail', $profileOwner->name));

        $response
            ->assertOk()
            ->assertSee('Visible Profile Review')
            ->assertDontSee('Hidden Profile Review')
            ->assertDontSee('Hidden profile review description.');
    }

    public function test_other_users_profile_uses_no_menu_layout_and_distinct_upload_count(): void
    {
        $profileOwner = User::factory()->create();
        $viewer = User::factory()->create();

        $firstPost = Posts::factory()->create(['title' => 'First Upload']);
        $secondPost = Posts::factory()->create(['title' => 'Second Upload']);
        $thirdPost = Posts::factory()->create(['title' => 'Rated Elsewhere']);

        UserPosts::factory()->create([
            'user_id' => $profileOwner->id,
            'post_id' => $firstPost->id,
            'description' => 'First upload description.',
            'user_hidden' => false,
        ]);
        UserPosts::factory()->create([
            'user_id' => $profileOwner->id,
            'post_id' => $secondPost->id,
            'description' => 'Second upload description.',
            'user_hidden' => false,
        ]);

        Ratings::factory()->count(3)->sequence(
            ['user_id' => $profileOwner->id, 'post_id' => $firstPost->id, 'rating' => 5],
            ['user_id' => $profileOwner->id, 'post_id' => $secondPost->id, 'rating' => 4],
            ['user_id' => $profileOwner->id, 'post_id' => $thirdPost->id, 'rating' => 3],
        )->create();

        $response = $this
            ->actingAs($viewer)
            ->get(route('profile-detail', $profileOwner->name));

        $response
            ->assertOk()
            ->assertSee('dashboard-page--no-menu', false)
            ->assertSee('My Reviews')
            ->assertSee('3')
            ->assertSee('My Uploads')
            ->assertSee('2')
            ->assertDontSee('x-layout.menu');
    }
}
