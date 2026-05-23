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
            ->assertSee('id="authShell"', false)
            ->assertSee('id="loginForm"', false)
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
            ->assertSee('id="authShell"', false)
            ->assertSee('id="registerForm"', false)
            ->assertSee('id="registrationModal"', false)
            ->assertSee('id="profileForm"', false)
            ->assertSee('id="confirmRegisterBtn"', false);
    }
}
