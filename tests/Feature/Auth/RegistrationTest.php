<?php

namespace Tests\Feature\Auth;

use App\Mail\OtpVerificationMail;
use App\Models\OtpVerifications;
use App\Models\User;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use SweetAlert2\Laravel\Swal;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_standard_registration_post_does_not_create_user_without_otp(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
        $response->assertRedirect(route('register', absolute: false))
            ->assertSessionHasErrors('email');
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

        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertDatabaseHas('otpVerifications', [
            'email' => 'test@example.com',
            'user_id' => null,
            'is_verified' => false,
        ]);
        $this->assertDatabaseCount('settings', 0);
        $this->assertDatabaseCount('user_current_fonts', 0);
    }

    public function test_registration_resend_otp_sends_otp_email(): void
    {
        Config::set('mail.mailers.smtp.password', 'test-app-password');
        Mail::fake();

        $this->postJson('/register/initiate', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password!!!',
        ])->assertOk();

        $response = $this->postJson('/register/resend-otp');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonMissingPath('otp');

        Mail::assertSentTimes(OtpVerificationMail::class, 2);
        $this->assertDatabaseCount('users', 0);
        $this->assertSame(2, OtpVerifications::where('email', 'test@example.com')->count());
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

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_verify_otp_marks_session_as_verified_without_creating_user(): void
    {
        Config::set('mail.mailers.smtp.password', 'test-app-password');
        Mail::fake();

        $this->postJson('/register/initiate', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password!!!',
        ])->assertOk();

        $verification = OtpVerifications::where('email', 'test@example.com')->firstOrFail();

        $response = $this->postJson('/register/verify-otp', [
            'otp_code' => $verification->otp,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertTrue((bool) $verification->fresh()->is_verified);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_finalize_creates_user_after_verified_otp_and_flashes_toast(): void
    {
        Config::set('mail.mailers.smtp.password', 'test-app-password');
        Mail::fake();

        $this->postJson('/register/initiate', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password!!!',
        ])->assertOk();

        $verification = OtpVerifications::where('email', 'test@example.com')->firstOrFail();

        $this->postJson('/register/verify-otp', [
            'otp_code' => $verification->otp,
        ]);

        $response = $this->postJson('/register/finalize', [
            'user_dob' => null,
            'user_phone' => '',
            'user_bio' => '',
        ]);

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertGuest();
        $this->assertTrue((bool) $user->is_verified);
        $this->assertUserHasDefaultPreferences($user);
        $this->assertSame($user->id, $verification->fresh()->user_id);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'redirect' => route('login', absolute: false),
            ])
            ->assertSessionHas(Swal::SESSION_KEY, function (array $toast): bool {
                return $toast['toast'] === true
                    && $toast['position'] === 'top-end'
                    && $toast['showConfirmButton'] === false
                    && $toast['icon'] === 'success'
                    && $toast['title'] === 'Account ready';
            });
    }

    public function test_registration_finalize_requires_verified_otp(): void
    {
        Config::set('mail.mailers.smtp.password', 'test-app-password');
        Mail::fake();

        $this->postJson('/register/initiate', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password!!!',
        ])->assertOk();

        $response = $this->postJson('/register/finalize', [
            'user_dob' => null,
            'user_phone' => '',
            'user_bio' => '',
        ]);

        $response->assertForbidden()
            ->assertJson([
                'message' => 'OTP verification must be completed first.',
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
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
