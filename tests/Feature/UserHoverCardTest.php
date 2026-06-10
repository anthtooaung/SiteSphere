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

class UserHoverCardTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
        $this->seed(ThemesSeeder::class);
    }

    public function test_hover_card_returns_correct_user_data_and_calculated_stats(): void
    {
        $user = User::factory()->create([
            'name' => 'Alice Tester',
            'email' => 'alice@example.com',
            'user_phone' => '123-456-7890',
        ]);

        $post1 = Posts::factory()->create();
        $post2 = Posts::factory()->create();

        // Create uploads (UserPosts) for this user
        UserPosts::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post1->id,
            'description' => 'Alice review of post 1',
        ]);

        UserPosts::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post2->id,
            'description' => 'Alice review of post 2',
        ]);

        // Alice receives ratings on these posts
        Ratings::factory()->create([
            'user_id' => User::factory()->create()->id,
            'post_id' => $post1->id,
            'rating' => 5,
        ]);

        Ratings::factory()->create([
            'user_id' => User::factory()->create()->id,
            'post_id' => $post2->id,
            'rating' => 4,
        ]);

        $userPostQueries = 0;

        DB::listen(function ($query) use (&$userPostQueries): void {
            $sql = strtolower($query->sql);

            if (str_contains($sql, 'from "user_posts"') || str_contains($sql, 'from `user_posts`')) {
                $userPostQueries++;
            }
        });

        $response = $this->get(route('users.hover-card', $user->id));

        $response->assertOk();
        $response->assertViewIs('components.layout.hover-profile-card');

        // Check user details are visible
        $response->assertSee('Alice Tester');
        $response->assertSee('alice@example.com');
        $response->assertSee('123-456-7890');

        // Check stats: Uploads should be 2, Rating average should be 4.5
        $response->assertSee('2');
        $response->assertSee('4.5');
        $this->assertSame(1, $userPostQueries);
    }
}
