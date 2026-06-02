<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_dashboard_renders_fixed_nav_and_layout_menu(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('class="desktop-nav flex items-center"', false)
            ->assertSee('id="layoutMenu"', false)
            ->assertSee('dashboard-page--left', false)
            ->assertSee('layout-menu--left', false)
            ->assertSee('data-menu-bar-location="left"', false)
            ->assertSee('Dashboard')
            ->assertSee('Welcome back, '.$user->name);
    }

    public function test_dashboard_menu_uses_each_valid_menu_bar_location(): void
    {
        foreach (['left', 'right', 'top', 'bottom'] as $location) {
            $user = User::factory()->create(['role' => 'user']);

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
                ->assertSee('data-menu-bar-location="'.$location.'"', false);
        }
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
}
