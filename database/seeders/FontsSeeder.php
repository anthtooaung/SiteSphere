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
