<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AppLogoComponentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_app_logo_renders_sitesphere_svg_using_the_accent_color(): void
    {
        $view = $this->blade('<x-app-logo />');

        $view
            ->assertSee('<svg', false)
            ->assertSee('viewBox="0 0 88.5 99.5"', false)
            ->assertSee('role="img"', false)
            ->assertSee('aria-label="SiteSphere"', false)
            ->assertSee('color: var(--accent-color, #6c5ce7);', false)
            ->assertSee('fill="var(--accent-color, #6c5ce7)"', false)
            ->assertDontSee('fill="currentColor"', false)
            ->assertDontSee('fill="#2EB4F7"', false)
            ->assertDontSee('>S</div>', false);
    }

    public function test_app_logo_keeps_caller_size_classes(): void
    {
        $view = $this->blade('<x-app-logo class="size-6" />');

        $view->assertSee('size-6', false);
    }
}
