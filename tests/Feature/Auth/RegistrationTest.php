<?php

namespace Tests\Feature\Auth;

use App\Mail\OtpVerificationMail;
use App\Models\OtpVerifications;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use SweetAlert2\Laravel\Swal;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_initiate_sends_otp_email_without_exposing_otp_code(): void
    {
        Config::set('mail.mailers.smtp.password', 'test-app-password');
        Mail::fake();

        $response = $this->postJson('/register/initiate', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password!!!',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'email' => 'test@example.com',
            ])
            ->assertJsonMissingPath('otp');

        Mail::assertSent(OtpVerificationMail::class, function (OtpVerificationMail $mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_registration_resend_otp_sends_otp_email(): void
    {
        Config::set('mail.mailers.smtp.password', 'test-app-password');
        Mail::fake();

        $user = User::factory()->unverified()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/register/resend-otp', [
            'user_id' => $user->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonMissingPath('otp');

        Mail::assertSent(OtpVerificationMail::class, function (OtpVerificationMail $mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_registration_initiate_still_returns_success_when_otp_email_delivery_fails(): void
    {
        Config::set('mail.mailers.smtp.password', 'test-app-password');
        Mail::shouldReceive('to')
            ->once()
            ->with('test@example.com')
            ->andReturnSelf();

        Mail::shouldReceive('send')
            ->once()
            ->withArgs(function (OtpVerificationMail $mail): bool {
                return $mail->otpCode !== '';
            })
            ->andThrow(new TransportException('SMTP auth failed.'));

        $response = $this->postJson('/register/initiate', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password!!!',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'email' => 'test@example.com',
                'otp_delivery_failed' => true,
            ])
            ->assertJsonMissingPath('otp');
    }

    public function test_registration_finalize_redirects_to_login_and_flashes_database_positioned_toast(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'test@example.com',
        ]);
        $this->createSettingsFor($user, 'bottom-end');

        OtpVerifications::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'is_verified' => true,
            'expire_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/register/finalize', [
            'user_id' => $user->id,
            'user_dob' => null,
            'user_phone' => '',
            'user_bio' => '',
        ]);

        $this->assertGuest();
        $this->assertTrue((bool) $user->fresh()->is_verified);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'redirect' => route('login', absolute: false),
            ])
            ->assertSessionHas(Swal::SESSION_KEY, function (array $toast): bool {
                return $toast['toast'] === true
                    && $toast['position'] === 'bottom-end'
                    && $toast['showConfirmButton'] === false
                    && $toast['icon'] === 'success'
                    && $toast['title'] === 'Account ready';
            });
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
