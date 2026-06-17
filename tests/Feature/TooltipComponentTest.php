<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TooltipComponentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_tooltip_renders_content_correctly(): void
    {
        $view = $this->blade('<x-tooltip content="Tooltip Content">Button</x-tooltip>');

        $view
            ->assertSee('Tooltip Content')
            ->assertSee('Button')
            ->assertSee('role="tooltip"', false)
            ->assertSee('x-data="{ show: false }"', false);
    }
}
