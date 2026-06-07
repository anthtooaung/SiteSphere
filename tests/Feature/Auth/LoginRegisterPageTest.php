<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRegisterPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_the_dom_hooks_required_by_the_auth_script(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('<title>', false)
            ->assertSee('Login')
            ->assertSee('id="authShell"', false)
            ->assertSee('id="loginForm"', false)
            ->assertSee(route('social.redirect', 'google'), false)
            ->assertSee(route('social.redirect', 'github'), false)
            ->assertSee(route('password.request'), false)
            ->assertSee('id="toggleLoginPassword"', false)
            ->assertSee('id="registerForm"', false)
            ->assertSee('id="showLogin"', false)
            ->assertSee('id="showRegister"', false)
            ->assertSee('id="otpForm"', false)
            ->assertSee('id="profileForm"', false);
    }

    public function test_register_page_renders_the_dom_hooks_required_by_the_auth_script(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('<title>', false)
            ->assertSee('Register')
            ->assertSee('id="authShell"', false)
            ->assertSee('class="brand-title"', false)
            ->assertSee('class="brand-sphere"', false)
            ->assertSee('id="registerForm"', false)
            ->assertSee('id="toggleRegisterPassword"', false)
            ->assertSee('id="toggleRegisterConfirmPassword"', false)
            ->assertSee('id="registrationModal"', false)
            ->assertSee('id="profileForm"', false)
            ->assertSee('id="profile-phone"', false)
            ->assertSee('data-phone-format', false)
            ->assertSee('placeholder="9 123 456 789"', false)
            ->assertSee('Create Your Account')
            ->assertSee('Complete each step to finish setting up your SiteSphere account securely.')
            ->assertSee('id="confirmRegisterBtn"', false);
    }

    public function test_register_page_renders_loading_markup_for_register_flow_buttons(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('data-loading-button="register"', false)
            ->assertSee('data-loading-button="verify-otp"', false)
            ->assertSee('data-loading-button="continue-profile"', false)
            ->assertSee('data-loading-button="confirm-account"', false)
            ->assertSee('<span class="button-label">Register</span>', false)
            ->assertSee('<span class="button-label">Verify OTP</span>', false)
            ->assertSee('<span class="button-label">Continue</span>', false)
            ->assertSee('<span class="button-label">Confirm account</span>', false)
            ->assertSee('class="button-loader"', false)
            ->assertSee('<i></i><i></i><i></i>', false);
    }

    public function test_register_button_loading_assets_include_css_and_script_hooks(): void
    {
        $authCss = file_get_contents(resource_path('css/auth.css'));
        $authJs = file_get_contents(resource_path('js/auth.js'));

        $this->assertStringContainsString('.primary-button.is-loading .button-loader', $authCss);
        $this->assertStringContainsString('@keyframes authButtonDotPulse', $authCss);
        $this->assertStringContainsString('const setButtonLoading', $authJs);
        $this->assertStringContainsString('aria-busy', $authJs);
        $this->assertStringContainsString('data-loading-button="register"', $authJs);
        $this->assertStringContainsString('data-loading-button="verify-otp"', $authJs);
    }
}
