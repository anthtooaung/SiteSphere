<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('security'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_dashboard_shell_security_page(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);

        $user->settings()->update([
            'user_post_visible' => true,
        ]);

        $this->actingAs($user)
            ->get(route('security'))
            ->assertOk()
            ->assertSee('data-security-page', false)
            ->assertSee('dashboard-page--left', false)
            ->assertSee('resources/css/security.css', false)
            ->assertSee('Two-Factor Authentication')
            ->assertSee('name="two_factor_enabled"', false)
            ->assertSee('data-security-two-factor', false)
            ->assertSee('name="user_post_visible"', false)
            ->assertSee('data-security-visibility', false)
            ->assertSee('id="current-password"', false)
            ->assertSee('id="new-password"', false)
            ->assertSee('id="confirm-password"', false)
            ->assertSee('Save Changes');
    }

    public function test_security_toggles_are_saved(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
        ]);

        $user->settings()->update([
            'user_post_visible' => false,
        ]);

        $this->actingAs($user)
            ->patch(route('security.update'), [
                'two_factor_enabled' => '1',
                'user_post_visible' => '1',
            ])
            ->assertRedirect(route('security'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Security settings saved successfully.');

        $user->refresh();

        $this->assertTrue($user->two_factor_enabled);
        $this->assertTrue((bool) $user->settings()->value('user_post_visible'));
    }

    public function test_password_can_be_changed_from_security_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('security.update'), [
                'two_factor_enabled' => '0',
                'user_post_visible' => '0',
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('security'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_current_password_must_be_correct_to_change_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('security.update'), [
                'two_factor_enabled' => '0',
                'user_post_visible' => '0',
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_invalid_password_confirmation_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('security.update'), [
                'two_factor_enabled' => '0',
                'user_post_visible' => '0',
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_ajax_security_toggles_are_saved_returns_json(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
        ]);

        $user->settings()->update([
            'user_post_visible' => false,
        ]);

        $this->actingAs($user)
            ->patchJson(route('security.update'), [
                'two_factor_enabled' => '1',
                'user_post_visible' => '1',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Security settings saved successfully.',
            ]);

        $user->refresh();

        $this->assertTrue($user->two_factor_enabled);
        $this->assertTrue((bool) $user->settings()->value('user_post_visible'));
    }
}
