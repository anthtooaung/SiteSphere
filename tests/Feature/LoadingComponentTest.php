<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoadingComponentTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_loading_component_renders_the_sitesphere_draw_loader(): void
    {
        $view = $this->blade('<x-loading />');

        $view
            ->assertSee('data-page-loader', false)
            ->assertSee('sitesphere-loader__draw', false)
            ->assertSee('pathLength="100"', false)
            ->assertSee('M44.5 28.75L28.75 37.25', false)
            ->assertSee('M43.25 6L6.25 27.75', false)
            ->assertSee('var(--accent-color, #6c5ce7)', false)
            ->assertSee('stroke-dashoffset: 100', false)
            ->assertSee('.sitesphere-loader.is-active .sitesphere-loader__draw-a', false)
            ->assertSee('.sitesphere-loader.is-active .sitesphere-loader__draw-b', false)
            ->assertSee('animation-fill-mode: both', false)
            ->assertSee('void loader.offsetWidth', false)
            ->assertSee('minimumVisibleMilliseconds = 300', false)
            ->assertSee('shownAt', false)
            ->assertSee('hideTimeout', false)
            ->assertSee('Date.now()', false)
            ->assertSee('setTimeout', false)
            ->assertDontSee('#2eb4f7', false)
            ->assertSee('window.siteSpherePageLoaderInitialized', false)
            ->assertSee("performance.getEntriesByType('navigation')", false)
            ->assertSee("type === 'reload'", false)
            ->assertSee('beforeunload', false)
            ->assertSee('pagehide', false)
            ->assertSee('load', false)
            ->assertSee('pageshow', false);
    }

    public function test_guest_welcome_page_includes_loading_overlay(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('data-page-loader', false)
            ->assertSee('var(--accent-color, #6c5ce7)', false);
    }

    public function test_authenticated_dashboard_includes_loading_overlay(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('data-page-loader', false)
            ->assertSee('sitesphere-loader__draw', false);
    }

    public function test_authenticated_home_page_includes_loading_overlay(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('data-page-loader', false)
            ->assertSee('sitesphere-loader__draw', false);
    }
}
