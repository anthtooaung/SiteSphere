<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityPageTest extends TestCase
{
    use LazilyRefreshDatabase;

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

        $response = $this->actingAs($user)
            ->get(route('security'))
            ->assertOk();

        $html = $response->getContent();
        $this->assertTrue(
            str_contains($html, 'build/assets/security-') || str_contains($html, 'resources/css/security.css'),
            'Failed asserting that security.css is loaded'
        );

        $response->assertSee('data-security-page', false)
            ->assertSee('dashboard-page--left', false)
            ->assertSee('Two-Factor Authentication')
            ->assertSee('name="two_factor_enabled"', false)
            ->assertSee('data-security-two-factor', false)
            ->assertSee('name="user_post_visible"', false)
            ->assertSee('data-security-visibility', false)
            ->assertSee('id="current-password"', false)
            ->assertSee('id="new-password"', false)
            ->assertSee('id="confirm-password"', false)
            ->assertSee('Save Changes')
            ->assertSee(":class=\"{ 'is-loading': isSubmitting }\"", false)
            ->assertSee(':disabled="isSubmitting"', false);
    }

    public function test_password_change_is_disabled_for_oauth_only_users(): void
    {
        $user = User::factory()->create([
            'password_set' => false,
        ]);

        $user->socialAccounts()->create([
            'provider' => 'google',
            'provider_id' => 'google-123',
        ]);

        $this->assertTrue($user->isOauthOnly());

        $this->actingAs($user)
            ->get(route('security'))
            ->assertOk()
            ->assertSee('disabled title="Password is managed via OAuth"', false)
            ->assertSee('Password management is handled by your linked social account.');
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
                'password' => 'SecretPassword123!',
                'password_confirmation' => 'SecretPassword123!',
            ])
            ->assertRedirect(route('security'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('SecretPassword123!', $user->refresh()->password));
    }

    public function test_current_password_must_be_correct_to_change_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('security.update'), [
                'two_factor_enabled' => '0',
                'user_post_visible' => '0',
                'current_password' => 'wrong-password',
                'password' => 'SecretPassword123!',
                'password_confirmation' => 'SecretPassword123!',
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
                'password' => 'SecretPassword123!',
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
