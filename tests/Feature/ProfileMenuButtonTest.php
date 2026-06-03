<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ProfileMenuButtonTest extends TestCase
{
    use RefreshDatabase;

    private const MOBILE_USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_regular_user_sees_profile_menu_without_admin_links(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('data-dropdown-toggle="desktopAccountMenu"', false)
            ->assertSee('id="desktopAccountMenu"', false)
            ->assertDontSee('data-layout-menu-trigger', false)
            ->assertDontSee('id="layoutMenu"', false)
            ->assertSee('View Profile')
            ->assertSee('Saved Post')
            ->assertSee('href="'.route('saved-post').'"', false)
            ->assertSee('Setting')
            ->assertSee('Edit Profile')
            ->assertSee('Appearance')
            ->assertSee('href="'.route('appearance').'"', false)
            ->assertSee('Security')
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Logout')
            ->assertDontSee('href="'.route('dashboard').'"', false)
            ->assertDontSee('Dashboard')
            ->assertDontSee('Users')
            ->assertDontSee('Reports');
    }

    public function test_admin_user_sees_admin_profile_menu_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/home');

        $response
            ->assertOk()
            ->assertDontSee('data-layout-menu-trigger', false)
            ->assertDontSee('id="layoutMenu"', false)
            ->assertSee('View Profile')
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee('Dashboard')
            ->assertSee('Users')
            ->assertSee('href="'.route('users').'"', false)
            ->assertSee('Reports')
            ->assertSee('href="'.route('reports').'"', false)
            ->assertSee('Saved Post')
            ->assertSee('href="'.route('saved-post').'"', false)
            ->assertSee('Edit Profile')
            ->assertSee('Appearance')
            ->assertSee('href="'.route('appearance').'"', false)
            ->assertSee('Security')
            ->assertSee('Logout');
    }

    public function test_mobile_profile_menu_renders_mobile_dropdown_target(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->getAsAuthenticatedMobileUser($user, '/home');

        $response
            ->assertOk()
            ->assertSee('data-dropdown-toggle="mobileAccountMenu"', false)
            ->assertSee('data-dropdown-placement="top"', false)
            ->assertSee('id="mobileAccountMenu"', false)
            ->assertDontSee('id="layoutMenu"', false)
            ->assertDontSee('data-layout-menu-trigger', false)
            ->assertSee('View Profile')
            ->assertSee('Saved Post')
            ->assertSee('href="'.route('saved-post').'"', false)
            ->assertSee('Edit Profile')
            ->assertSee('Appearance')
            ->assertSee('href="'.route('appearance').'"', false)
            ->assertSee('Security')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Logout')
            ->assertDontSee('href="'.route('dashboard').'"', false)
            ->assertDontSee('Dashboard')
            ->assertDontSee('Users')
            ->assertDontSee('Reports');
    }

    public function test_admin_dashboard_profile_menu_link_marks_current_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee('class="account-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_saved_post_profile_menu_link_marks_current_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('saved-post'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('saved-post').'"', false)
            ->assertSee('class="account-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_users_profile_menu_link_marks_current_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('users'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('users').'"', false)
            ->assertSee('class="account-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_reports_profile_menu_link_marks_current_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('reports'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('reports').'"', false)
            ->assertSee('class="account-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_appearance_profile_menu_link_marks_current_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('appearance'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('appearance').'"', false)
            ->assertSee('class="account-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

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
}
