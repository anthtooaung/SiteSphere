<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_home_to_login(): void
    {
        $response = $this->get('/home');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_home_with_active_desktop_home_navigation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('This is the Home Page of content Section')
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('class="desktop-link active"', false)
            ->assertSee('aria-current="page"', false);
    }
}
