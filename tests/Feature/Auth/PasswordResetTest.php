<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordResetOtpMail;
use App\Models\OtpVerifications;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_reset_password_otp_request_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200)
            ->assertSee('Forgot password?')
            ->assertSee('Send OTP');
    }

    public function test_reset_password_otp_can_be_requested(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertRedirect(route('password.reset'))
            ->assertSessionHas('password_reset_user_id', $user->id)
            ->assertSessionHas('password_reset_email', $user->email);

        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->otpCode !== '';
        });

        $this->assertDatabaseCount((new OtpVerifications)->getTable(), 1);
    }

    public function test_reset_password_otp_request_requires_known_email(): void
    {
        Mail::fake();

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'missing@example.com',
        ]);

        $response->assertRedirect('/forgot-password')
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_reset_password_otp_screen_can_be_rendered_after_request(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withSession([
                'password_reset_user_id' => $user->id,
                'password_reset_email' => $user->email,
            ])
            ->get('/reset-password-otp');

        $response->assertStatus(200)
            ->assertSee('Enter your OTP')
            ->assertSee('id="resetOtpForm"', false)
            ->assertSee('class="otp-code-row"', false)
            ->assertSee('class="plain-input otp-digit"', false)
            ->assertSee($user->email);
    }

    public function test_reset_password_otp_screen_redirects_without_reset_session(): void
    {
        $response = $this->get('/reset-password-otp');

        $response->assertRedirect(route('password.request'));
    }

    public function test_valid_otp_redirects_to_new_password_screen(): void
    {
        $user = User::factory()->create();
        $currentPassword = $user->password;
        $verification = OtpVerifications::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        $response = $this
            ->withSession([
                'password_reset_user_id' => $user->id,
                'password_reset_email' => $user->email,
            ])
            ->post('/reset-password-otp', [
                'otp_code' => '123456',
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('password.new'))
            ->assertSessionHas('password_reset_otp_verified', true);

        $this->assertTrue($verification->fresh()->is_verified);
        $this->assertSame($currentPassword, $user->fresh()->password);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_reset_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        OtpVerifications::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        $response = $this
            ->withSession([
                'password_reset_user_id' => $user->id,
                'password_reset_email' => $user->email,
            ])
            ->from('/reset-password-otp')
            ->post('/reset-password-otp', [
                'otp_code' => '654321',
            ]);

        $response->assertRedirect('/reset-password-otp')
            ->assertSessionHasErrors('otp_code');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_password_reset_fails_with_expired_otp(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        OtpVerifications::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'is_verified' => false,
            'expire_at' => now()->subMinute(),
        ]);

        $response = $this
            ->withSession([
                'password_reset_user_id' => $user->id,
                'password_reset_email' => $user->email,
            ])
            ->from('/reset-password-otp')
            ->post('/reset-password-otp', [
                'otp_code' => '123456',
            ]);

        $response->assertRedirect('/reset-password-otp')
            ->assertSessionHasErrors('otp_code');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_new_password_screen_requires_verified_otp(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withSession([
                'password_reset_user_id' => $user->id,
                'password_reset_email' => $user->email,
            ])
            ->get('/reset-password-new');

        $response->assertRedirect(route('password.reset'));
    }

    public function test_new_password_screen_can_be_rendered_after_otp_verification(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withSession([
                'password_reset_user_id' => $user->id,
                'password_reset_email' => $user->email,
                'password_reset_otp_verified' => true,
            ])
            ->get('/reset-password-new');

        $response->assertStatus(200)
            ->assertSee('Create new password')
            ->assertSee('id="toggleResetPassword"', false)
            ->assertSee('id="toggleResetPasswordConfirmation"', false)
            ->assertSee($user->email);
    }

    public function test_password_can_be_reset_after_verified_otp(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withSession([
                'password_reset_user_id' => $user->id,
                'password_reset_email' => $user->email,
                'password_reset_otp_verified' => true,
            ])
            ->post('/reset-password', [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $response->assertSessionMissing('password_reset_user_id')
            ->assertSessionMissing('password_reset_email')
            ->assertSessionMissing('password_reset_otp_verified');
    }

    public function test_password_reset_store_requires_verified_otp(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this
            ->withSession([
                'password_reset_user_id' => $user->id,
                'password_reset_email' => $user->email,
            ])
            ->post('/reset-password', [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertRedirect(route('password.reset'));

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
