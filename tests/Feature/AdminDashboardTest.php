<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\Reports;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_members_see_personal_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('My Reviews')
            ->assertSeeText('Saved Posts')
            ->assertSeeText('Ratings Given')
            ->assertDontSeeText('Admin Dashboard')
            ->assertDontSeeText('Total Users');
    }

    public function test_admins_see_admin_overview(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Admin Dashboard')
            ->assertSeeText('System Overview')
            ->assertSeeText('Total Users')
            ->assertSeeText('Total Reviews')
            ->assertSeeText('Total Reports');
    }

    public function test_admin_can_view_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $post = Posts::factory()->create();

        // Create specific counts using the models that DashboardController actually counts
        User::factory()->count(10)->create(['role' => 'user']);
        
        // Use UserPosts for "Total Reviews" as per DashboardController
        // Create 7 reviews for 7 DIFFERENT posts by the same user to avoid unique constraint
        UserPosts::factory()->count(7)->create([
            'user_id' => $user->id,
            'post_id' => fn() => Posts::factory()->create()->id,
        ]);

        Reports::factory()->count(4)->create([
            'user_id' => $user->id,
            'target_id' => $post->id,
            'target_name' => 'posts',
        ]);

        $expectedUserCount = User::count();
        $expectedReviewCount = UserPosts::count();
        $expectedReportCount = Reports::count();

        $response = $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();

        // Check if counts are visible in the response
        $response->assertSeeText((string)$expectedUserCount);
        $response->assertSeeText((string)$expectedReviewCount);
        $response->assertSeeText((string)$expectedReportCount);
    }

    public function test_top_posts_leaderboard_displays_correct_ranked_posts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create posts with different ratings
        $post1 = Posts::factory()->create(['title' => 'Top Post 1']);
        $post2 = Posts::factory()->create(['title' => 'Top Post 2']);
        $post3 = Posts::factory()->create(['title' => 'Top Post 3']);

        // Post 1: Avg 5.0
        Ratings::factory()->create(['post_id' => $post1->id, 'rating' => 5]);
        
        // Post 2: Avg 4.0
        Ratings::factory()->create(['post_id' => $post2->id, 'rating' => 4]);

        // Post 3: Avg 3.0
        Ratings::factory()->create(['post_id' => $post3->id, 'rating' => 3]);

        $response = $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();

        // Check if posts are in the correct order in the view
        $content = $response->getContent();
        $pos1 = strpos($content, 'Top Post 1');
        $pos2 = strpos($content, 'Top Post 2');
        $pos3 = strpos($content, 'Top Post 3');

        $this->assertTrue($pos1 < $pos2);
        $this->assertTrue($pos2 < $pos3);
    }
}
