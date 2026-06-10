<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FontCatalogTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_default_font_catalog_is_available_after_migrations(): void
    {
        $fonts = DB::table('fonts')
            ->orderBy('sort_order')
            ->get(['display_name', 'google_family', 'font_family', 'sort_order', 'is_default']);

        $this->assertCount(6, $fonts);
        $this->assertSame(
            ['Figtree', 'Inter', 'Poppins', 'Roboto', 'Open Sans', 'Nunito'],
            $fonts->pluck('display_name')->all(),
        );
        $this->assertSame('Figtree, sans-serif', $fonts->firstWhere('display_name', 'Figtree')->font_family);
        $this->assertSame('"Open Sans", sans-serif', $fonts->firstWhere('display_name', 'Open Sans')->font_family);
        $this->assertSame(1, (int) $fonts->where('is_default', true)->count());
        $this->assertTrue((bool) $fonts->firstWhere('display_name', 'Figtree')->is_default);
    }

    public function test_layout_loads_curated_google_fonts_once(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('https://fonts.googleapis.com/css2?family=Figtree', false);
        $response->assertSee('family=Open+Sans:wght@400;500;600;700;800;900', false);
        $response->assertSee('&display=swap', false);
    }

    public function test_auth_styles_use_the_global_font_variable(): void
    {
        $authStyles = file_get_contents(resource_path('css/auth.css'));

        $this->assertStringContainsString('font-family: var(--font-family, Figtree, sans-serif);', $authStyles);
        $this->assertStringNotContainsString('fonts.googleapis.com/css2?family=Inter', $authStyles);
        $this->assertStringNotContainsString('font-family: "Inter", Arial, sans-serif;', $authStyles);
    }

    public function test_tailwind_sans_utility_uses_the_global_font_variable(): void
    {
        $tailwindConfig = file_get_contents(base_path('tailwind.config.js'));

        $this->assertStringContainsString("sans: ['var(--font-family)', ...defaultTheme.fontFamily.sans]", $tailwindConfig);
        $this->assertStringNotContainsString("sans: ['Figtree', ...defaultTheme.fontFamily.sans]", $tailwindConfig);
    }
}
