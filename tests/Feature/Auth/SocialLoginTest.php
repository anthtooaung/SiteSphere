<?php

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use SweetAlert2\Laravel\Swal;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_redirect_sends_users_to_the_provider(): void
    {
        Socialite::fake('google');

        $response = $this->get(route('social.redirect', 'google'));

        $response->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_github_social_redirect_sends_users_to_github(): void
    {
        Socialite::fake('github');

        $response = $this->get(route('social.redirect', 'github'));

        $response->assertRedirect('https://socialite.fake/github/authorize');
    }

    public function test_login_page_displays_social_authentication_errors(): void
    {
        $response = $this
            ->followingRedirects()
            ->get(route('social.callback', 'github'));

        $response->assertSee('Unable to authenticate with this social provider.');
    }

    public function test_social_callback_logs_in_an_existing_linked_account(): void
    {
        $user = User::factory()->create();
        $this->createSettingsFor($user, 'bottom-start');

        SocialAccount::query()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => null,
        ]);

        Socialite::fake('google', $this->socialiteUser(
            id: 'google-123',
            email: null,
            avatar: 'https://example.com/google-avatar.png',
        ));

        $response = $this->get(route('social.callback', 'google'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('https://example.com/google-avatar.png', $user->fresh()->user_image);
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

    public function test_social_callback_links_an_existing_email_user(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        Socialite::fake('github', $this->socialiteUser(
            id: 'github-123',
            email: 'existing@example.com',
            token: 'github-token',
            avatar: 'https://example.com/github-avatar.png',
        ));

        $response = $this->get(route('social.callback', 'github'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('https://example.com/github-avatar.png', $user->fresh()->user_image);
        $this->assertDatabaseHas('socialAccounts', [
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github-123',
            'token' => 'github-token',
        ]);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_social_callback_creates_and_authenticates_a_new_user(): void
    {
        Socialite::fake('google', $this->socialiteUser(
            id: 'google-456',
            email: 'new@example.com',
            name: 'New Social User',
            avatar: 'https://example.com/avatar.png',
        ));

        $response = $this->get(route('social.callback', 'google'));

        $user = User::query()->where('email', 'new@example.com')->first();

        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame('https://example.com/avatar.png', $user->user_image);
        $this->assertUserHasDefaultPreferences($user);
        $this->assertDatabaseHas('socialAccounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-456',
        ]);
        $response
            ->assertRedirect(route('home', absolute: false))
            ->assertSessionHas(Swal::SESSION_KEY, function (array $toast): bool {
                return $toast['toast'] === true
                    && $toast['position'] === 'top-end'
                    && $toast['showConfirmButton'] === false
                    && $toast['icon'] === 'success'
                    && $toast['title'] === 'Signed in successfully';
            });
    }

    public function test_social_callback_rejects_new_users_without_provider_email(): void
    {
        Socialite::fake('google', $this->socialiteUser(id: 'google-789', email: null));

        $response = $this->get(route('social.callback', 'google'));

        $this->assertGuest();
        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');
    }

    public function test_social_callback_preserves_existing_profile_image(): void
    {
        $user = User::factory()->create([
            'user_image' => 'profile_images/uploaded-avatar.jpg',
        ]);

        SocialAccount::query()->create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github-456',
            'token' => null,
        ]);

        Socialite::fake('github', $this->socialiteUser(
            id: 'github-456',
            email: null,
            avatar: 'https://example.com/github-avatar.png',
        ));

        $response = $this->get(route('social.callback', 'github'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('profile_images/uploaded-avatar.jpg', $user->fresh()->user_image);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_unsupported_social_providers_are_not_routable(): void
    {
        $this->get('/auth/facebook/redirect')->assertNotFound();
        $this->get('/auth/facebook/callback')->assertNotFound();
    }

    private function socialiteUser(
        string $id,
        ?string $email = 'social@example.com',
        string $name = 'Social User',
        ?string $token = null,
        ?string $avatar = null,
    ): SocialiteUser {
        return (new SocialiteUser)->map([
            'id' => $id,
            'nickname' => 'socialuser',
            'name' => $name,
            'email' => $email,
            'avatar' => $avatar,
        ])->setToken($token);
    }

    private function createSettingsFor(User $user, string $notificationLocation): void
    {
        $themeId = DB::table('themes')->insertGetId([
            'accent_color' => '#6c5ce7',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')->updateOrInsert([
            'user_id' => $user->id,
        ], [
            'menuBar_location' => 'right',
            'noti_location' => $notificationLocation,
            'dark_mode' => false,
            'user_post_visible' => false,
            'theme_id' => $themeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assertUserHasDefaultPreferences(User $user): void
    {
        $themeId = DB::table('themes')
            ->where('accent_color', '#6c5ce7')
            ->value('id');

        $defaultFontId = DB::table('fonts')
            ->where('is_default', true)
            ->value('id');

        $this->assertNotNull($themeId);
        $this->assertNotNull($defaultFontId);

        $this->assertDatabaseHas('settings', [
            'user_id' => $user->id,
            'menuBar_location' => 'left',
            'noti_location' => 'top-end',
            'dark_mode' => false,
            'user_post_visible' => false,
            'theme_id' => $themeId,
        ]);

        $this->assertDatabaseHas('user_current_fonts', [
            'user_id' => $user->id,
            'font_id' => $defaultFontId,
        ]);
    }
}
