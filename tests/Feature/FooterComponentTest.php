<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class FooterComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_footer_renders_brand_auth_links_newsletter_and_social_links(): void
    {
        $html = Blade::render('<x-layout.footer />');

        $this->assertStringContainsString('SiteSphere', $html);
        $this->assertStringContainsString('href="'.route('login').'"', $html);
        $this->assertStringContainsString('href="'.route('register').'"', $html);
        $this->assertStringContainsString('Newsletter', $html);
        $this->assertStringContainsString('Email Address', $html);
        $this->assertStringContainsString('aria-label="LinkedIn"', $html);
        $this->assertStringContainsString('aria-label="Telegram"', $html);
        $this->assertStringContainsString('aria-label="GitHub"', $html);
        $this->assertStringNotContainsString('href="'.route('dashboard').'"', $html);
    }

    public function test_authenticated_footer_renders_dashboard_and_hides_guest_auth_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web');

        $html = Blade::render('<x-layout.footer />');

        $this->assertStringContainsString('SiteSphere', $html);
        $this->assertStringContainsString('href="'.route('dashboard').'"', $html);
        $this->assertStringNotContainsString('href="'.route('login').'"', $html);
        $this->assertStringNotContainsString('href="'.route('register').'"', $html);
    }
}
