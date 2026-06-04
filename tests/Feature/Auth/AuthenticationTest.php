<?php

namespace Tests\Feature\Auth;

use App\Mail\LoginTwoFactorOtpMail;
use App\Models\OtpVerifications;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

    public function test_two_factor_users_are_redirected_to_otp_challenge_after_valid_password(): void
    {
        Mail::fake();
        config(['mail.mailers.smtp.password' => 'testing-password']);

        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $this->assertGuest();
        $response
            ->assertRedirect(route('login.two-factor'))
            ->assertSessionHas('login.two_factor.user_id', $user->id)
            ->assertSessionHas('login.two_factor.remember', true);

        $this->assertDatabaseHas('otpVerifications', [
            'user_id' => $user->id,
            'email' => $user->email,
            'is_verified' => false,
        ]);

        Mail::assertSent(LoginTwoFactorOtpMail::class, $user->email);
    }

    public function test_invalid_two_factor_otp_does_not_authenticate(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);

        OtpVerifications::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => '123456',
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        $this->withSession([
            'login.two_factor.user_id' => $user->id,
            'login.two_factor.remember' => false,
        ])->post(route('login.two-factor.store'), [
            'otp_code' => '000000',
        ])->assertSessionHasErrors('otp_code');

        $this->assertGuest();
    }

    public function test_expired_two_factor_otp_does_not_authenticate(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);

        OtpVerifications::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => '123456',
            'is_verified' => false,
            'expire_at' => now()->subMinute(),
        ]);

        $this->withSession([
            'login.two_factor.user_id' => $user->id,
            'login.two_factor.remember' => false,
        ])->post(route('login.two-factor.store'), [
            'otp_code' => '123456',
        ])->assertSessionHasErrors('otp_code');

        $this->assertGuest();
    }

    public function test_valid_two_factor_otp_authenticates_and_clears_pending_session(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);
        $this->createSettingsFor($user, 'bottom-start');

        $verification = OtpVerifications::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => '123456',
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        $response = $this->withSession([
            'login.two_factor.user_id' => $user->id,
            'login.two_factor.remember' => true,
        ])->post(route('login.two-factor.store'), [
            'otp_code' => '123456',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($verification->fresh()->is_verified);
        $response
            ->assertRedirect(route('home', absolute: false))
            ->assertSessionMissing('login.two_factor.user_id')
            ->assertSessionMissing('login.two_factor.remember')
            ->assertSessionHas(Swal::SESSION_KEY, function (array $toast): bool {
                return $toast['toast'] === true
                    && $toast['position'] === 'bottom-start'
                    && $toast['icon'] === 'success'
                    && $toast['title'] === 'Signed in successfully';
            });
    }

    public function test_two_factor_resend_creates_a_new_otp(): void
    {
        Mail::fake();
        config(['mail.mailers.smtp.password' => 'testing-password']);

        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);

        $this->withSession([
            'login.two_factor.user_id' => $user->id,
            'login.two_factor.remember' => false,
        ])->post(route('login.two-factor.resend'))
            ->assertRedirect()
            ->assertSessionHas('status', 'A new login verification code was sent to your email.');

        $this->assertDatabaseHas('otpVerifications', [
            'user_id' => $user->id,
            'email' => $user->email,
            'is_verified' => false,
        ]);

        Mail::assertSent(LoginTwoFactorOtpMail::class, $user->email);
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
}
