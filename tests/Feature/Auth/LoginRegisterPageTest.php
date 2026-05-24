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
            ->assertSee('id="registerForm"', false)
            ->assertSee('id="toggleRegisterPassword"', false)
            ->assertSee('id="toggleRegisterConfirmPassword"', false)
            ->assertSee('id="registrationModal"', false)
            ->assertSee('id="profileForm"', false)
            ->assertSee('id="profile-phone"', false)
            ->assertSee('data-phone-format', false)
            ->assertSee('placeholder="+95 9 123 456 789"', false)
            ->assertSee('Create Your Account')
            ->assertSee('Complete each step to finish setting up your SiteSphere account securely.')
            ->assertSee('id="confirmRegisterBtn"', false);
    }
}
