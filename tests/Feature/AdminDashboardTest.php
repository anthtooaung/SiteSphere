<?php

namespace Tests\Feature;

use App\Models\Posts;
use App\Models\Reports;
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
            ->assertSeeText('Total Reviews')
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
            ->assertSee('var(--accent-color, #6c5ce7)', false) // Check for default accent color
            ->assertDontSee('--accent-color: #14b8a6', false) // Ensure the old color is not present
            ->assertSeeText('Admin Dashboard')
            ->assertSeeText('Admin Overview')
            ->assertSeeText('Total Users')
            ->assertSeeText('Reviews')
            ->assertSeeText('Reports');
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
            'post_id' => fn () => Posts::factory()->create()->id,
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
        $response->assertSeeText((string) $expectedUserCount);
        $response->assertSeeText((string) $expectedReviewCount);
        $response->assertSeeText((string) $expectedReportCount);
    }

    public function test_admin_dashboard_includes_trend_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return array_key_exists('userTrend', $stats) &&
                   array_key_exists('reviewTrend', $stats) &&
                   array_key_exists('reportTrend', $stats) &&
                   count($stats['userTrend']) === 10 &&
                   count($stats['reviewTrend']) === 10 &&
                   count($stats['reportTrend']) === 10;
        });
    }
}
