<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FontsSeeder extends Seeder
{
    /**
     * @return array<int, array{display_name: string, google_family: string, font_family: string, sort_order: int, is_default: bool}>
     */
    private function defaultFonts(): array
    {
        return [
            [
                'display_name' => 'Figtree',
                'google_family' => 'Figtree',
                'font_family' => 'Figtree, sans-serif',
                'sort_order' => 10,
                'is_default' => true,
            ],
            [
                'display_name' => 'Inter',
                'google_family' => 'Inter',
                'font_family' => '"Inter", sans-serif',
                'sort_order' => 20,
                'is_default' => false,
            ],
            [
                'display_name' => 'Poppins',
                'google_family' => 'Poppins',
                'font_family' => '"Poppins", sans-serif',
                'sort_order' => 30,
                'is_default' => false,
            ],
            [
                'display_name' => 'Roboto',
                'google_family' => 'Roboto',
                'font_family' => '"Roboto", sans-serif',
                'sort_order' => 40,
                'is_default' => false,
            ],
            [
                'display_name' => 'Open Sans',
                'google_family' => 'Open Sans',
                'font_family' => '"Open Sans", sans-serif',
                'sort_order' => 50,
                'is_default' => false,
            ],
            [
                'display_name' => 'Nunito',
                'google_family' => 'Nunito',
                'font_family' => '"Nunito", sans-serif',
                'sort_order' => 60,
                'is_default' => false,
            ],
            [
                'display_name' => 'Montserrat',
                'google_family' => 'Montserrat',
                'font_family' => '"Montserrat", sans-serif',
                'sort_order' => 70,
                'is_default' => false,
            ],
            [
                'display_name' => 'Lato',
                'google_family' => 'Lato',
                'font_family' => '"Lato", sans-serif',
                'sort_order' => 80,
                'is_default' => false,
            ],
            [
                'display_name' => 'Raleway',
                'google_family' => 'Raleway',
                'font_family' => '"Raleway", sans-serif',
                'sort_order' => 90,
                'is_default' => false,
            ],
            [
                'display_name' => 'Oswald',
                'google_family' => 'Oswald',
                'font_family' => '"Oswald", sans-serif',
                'sort_order' => 100,
                'is_default' => false,
            ],
            [
                'display_name' => 'Merriweather',
                'google_family' => 'Merriweather',
                'font_family' => '"Merriweather", serif',
                'sort_order' => 110,
                'is_default' => false,
            ],
            [
                'display_name' => 'Playfair Display',
                'google_family' => 'Playfair Display',
                'font_family' => '"Playfair Display", serif',
                'sort_order' => 120,
                'is_default' => false,
            ],
            [
                'display_name' => 'Lora',
                'google_family' => 'Lora',
                'font_family' => '"Lora", serif',
                'sort_order' => 130,
                'is_default' => false,
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->defaultFonts() as $font) {
            DB::table('fonts')->updateOrInsert(
                ['font_family' => $font['font_family']],
                [
                    ...$font,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
