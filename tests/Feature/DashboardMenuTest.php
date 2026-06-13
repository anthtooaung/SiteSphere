<?php

namespace Tests\Feature;

use App\Models\Bookmarks;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardMenuTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_dashboard_renders_edge_layout_menu(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('class="desktop-nav flex items-center"', false)
            ->assertSee('id="layoutMenu"', false)
            ->assertSee('dashboard-page--left', false)
            ->assertSee('layout-menu--left', false)
            ->assertSee('data-menu-bar-location="left"', false)
            ->assertSee('Dashboard')
            ->assertSee('Admin Dashboard')
            ->assertSee('System Overview');
    }

    public function test_admin_dashboard_shows_platform_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Create some data to see in stats
        User::factory()->count(5)->create(['role' => 'user']);
        
        $response = $this->actingAs($admin)->get(route('dashboard'));
        
        $response->assertOk()
            ->assertSeeText('Total Users')
            ->assertSeeText('Total Reviews')
            ->assertSeeText('Total Reports')
            ->assertSeeText('Recent Activity')
            ->assertSeeText('Top Posts');
    }

    public function test_dashboard_renders_user_stats_and_recent_reviews(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $reviewedPost = Posts::factory()->create(['title' => 'Reviewed Dashboard Site']);
        $savedPost = Posts::factory()->create();
        $hiddenPost = Posts::factory()->create(['title' => 'Hidden Dashboard Site']);

        UserPosts::factory()->create([
            'user_id' => $user->id,
            'post_id' => $reviewedPost->id,
            'user_hidden' => false,
        ]);
        UserPosts::factory()->create([
            'user_id' => $user->id,
            'post_id' => $hiddenPost->id,
            'user_hidden' => true,
        ]);
        UserPosts::factory()->create([
            'user_id' => $otherUser->id,
            'post_id' => Posts::factory()->create()->id,
            'user_hidden' => false,
        ]);
        Bookmarks::factory()->create([
            'user_id' => $user->id,
            'post_id' => $savedPost->id,
        ]);
        Ratings::factory()->create([
            'user_id' => $user->id,
            'post_id' => $reviewedPost->id,
        ]);
        Ratings::factory()->create([
            'user_id' => $user->id,
            'post_id' => $savedPost->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSeeText('My Reviews')
            ->assertSeeText('Saved Posts')
            ->assertSeeText('Ratings Given')
            ->assertSeeText('Reviewed Websites')
            ->assertSeeText('Recent reviews')
            ->assertSeeText('Reviewed Dashboard Site')
            ->assertDontSeeText('Hidden Dashboard Site');

        $this->assertStringContainsString('<strong>1</strong>', $response->getContent());
        $this->assertStringContainsString('<strong>2</strong>', $response->getContent());
    }

    public function test_dashboard_menu_uses_each_valid_menu_bar_location(): void
    {
        foreach (['left', 'right', 'top', 'bottom'] as $location) {
            $user = User::factory()->create(['role' => 'admin']);

            DB::table('settings')
                ->where('user_id', $user->id)
                ->update([
                    'menuBar_location' => $location,
                    'updated_at' => now(),
                ]);

            $response = $this->actingAs($user)->get(route('dashboard'));

            $response
                ->assertOk()
                ->assertSee('dashboard-page--'.$location, false)
                ->assertSee('layout-menu--'.$location, false)
                ->assertSee('data-menu-bar-location="'.$location.'"', false)
                ->assertSee('Users')
                ->assertSee('href="'.route('users').'"', false)
                ->assertSee('Reports')
                ->assertSee('href="'.route('reports').'"', false)
                ->assertSee('aria-current="page"', false);

            if (in_array($location, ['top', 'bottom'], true)) {
                $response->assertSee('layout-menu--horizontal', false);
            } else {
                $response->assertDontSee('layout-menu--horizontal', false);
            }

            if (in_array($location, ['top', 'bottom'], true)) {
                $response
                    ->assertSee('layout-menu--topbar', false)
                    ->assertSee('class="layout-menu-topbar-link active"', false)
                    ->assertSee('id="layoutMenuSettingDropdown"', false)
                    ->assertSee('class="layout-menu-topbar-logout"', false);
            } else {
                $response
                    ->assertSee('class="layout-menu-link active"', false)
                    ->assertDontSee('layout-menu--topbar', false)
                    ->assertDontSee('id="layoutMenuSettingDropdown"', false)
                    ->assertDontSee('class="layout-menu-topbar-logout"', false);
            }
        }
    }

    public function test_dashboard_menu_hides_admin_group_for_normal_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user);

        $this->blade('<x-layout.menu />')
            ->assertSee('View Profile')
            ->assertSee('Saved Post')
            ->assertSee('href="'.route('saved-post').'"', false)
            ->assertSee('Appearance')
            ->assertSee('href="'.route('appearance').'"', false)
            ->assertSee('Security')
            ->assertSee('href="'.route('security').'"', false)
            ->assertSee('Edit Tag')
            ->assertSee('href="'.route('edit-tag').'"', false)
            ->assertSee('Edit Profile')
            ->assertSee('href="'.route('edit-profile').'"', false)
            ->assertSee('Logout')
            ->assertDontSee('Dashboard')
            ->assertDontSee('Users')
            ->assertDontSee('Reports')
            ->assertDontSee('aria-current="page"', false)
            ->assertDontSee('class="layout-menu-link active"', false);
    }

    public function test_dashboard_menu_falls_back_to_left_for_invalid_or_missing_settings(): void
    {
        $componentUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($componentUser);

        $this->blade('<x-layout.menu menu-bar-location="sideways" />')
            ->assertSee('layout-menu--left', false)
            ->assertSee('data-menu-bar-location="left"', false);

        $user = User::factory()->create(['role' => 'user']);

        DB::table('settings')
            ->where('user_id', $user->id)
            ->delete();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('dashboard-page--left', false)
            ->assertSee('layout-menu--left', false)
            ->assertSee('data-menu-bar-location="left"', false);
    }

    public function test_saved_post_layout_menu_link_marks_current_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('saved-post'))
            ->assertOk()
            ->assertSee('href="'.route('saved-post').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_users_layout_menu_link_marks_current_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('users'))
            ->assertOk()
            ->assertSee('href="'.route('users').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_reports_layout_menu_link_marks_current_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('reports'))
            ->assertOk()
            ->assertSee('href="'.route('reports').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_appearance_layout_menu_link_marks_current_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('appearance'))
            ->assertOk()
            ->assertSee('href="'.route('appearance').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_edit_profile_layout_menu_link_marks_current_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('edit-profile'))
            ->assertOk()
            ->assertSee('href="'.route('edit-profile').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_security_layout_menu_link_marks_current_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('security'))
            ->assertOk()
            ->assertSee('href="'.route('security').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_edit_tag_layout_menu_link_marks_current_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('edit-tag'))
            ->assertOk()
            ->assertSee('href="'.route('edit-tag').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_admin_dashboard_shows_top_posts_with_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = \App\Models\Categories::factory()->create(['name' => 'Test Category']);
        $tag = \App\Models\Tags::factory()->create();
        $tag->categories()->attach($category);
        
        $post = Posts::factory()->create(['title' => 'Top Rated Post']);
        $post->tags()->attach($tag);
        
        Ratings::factory()->create([
            'post_id' => $post->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        
        $response->assertOk()
            ->assertSeeText('Top Rated Post')
            ->assertSeeText('Test Category');
    }
}
