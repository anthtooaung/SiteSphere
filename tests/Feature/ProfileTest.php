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
            ->get('/profile');

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
            ->get('/profile');

        $response
            ->assertOk()
            ->assertSee('Profile Review 1')
            ->assertSee('Profile Review 4')
            ->assertSee('★ 1.0', false)
            ->assertSee('★ 4.0', false);

        $this->assertLessThanOrEqual(3, $ratingQueries);
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
            ->get(route('profile-detail', $profileOwner->slug));

        $response
            ->assertOk()
            ->assertSee('Visible Profile Review')
            ->assertDontSee('Hidden Profile Review')
            ->assertDontSee('Hidden profile review description.');
    }
}
