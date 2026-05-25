<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use SweetAlert2\Laravel\Swal;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();
        $this->createSettingsFor($user, 'bottom-start');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response
            ->assertRedirect(route('home', absolute: false))
            ->assertSessionHas(Swal::SESSION_KEY, function (array $toast): bool {
                return $toast['toast'] === true
                    && $toast['position'] === 'bottom-start'
                    && $toast['showConfirmButton'] === false
                    && $toast['icon'] === 'success'
                    && $toast['title'] === 'Signed in successfully';
            });
    }

    public function test_users_can_authenticate_with_uppercase_email_input(): void
    {
        $user = User::factory()->create([
            'email' => 'person@example.com',
        ]);
        $this->createSettingsFor($user, 'bottom-start');

        $response = $this->post('/login', [
            'email' => 'PERSON@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    private function createSettingsFor(User $user, string $notificationLocation): void
    {
        $themeId = DB::table('themes')->insertGetId([
            'accent_color' => '#6c5ce7',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')->insert([
            'user_id' => $user->id,
            'menuBar_location' => 'right',
            'noti_location' => $notificationLocation,
            'dark_mode' => false,
            'user_post_visible' => false,
            'theme_id' => $themeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
