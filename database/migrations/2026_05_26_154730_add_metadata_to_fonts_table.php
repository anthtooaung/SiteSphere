<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fonts', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('id');
            $table->string('google_family')->nullable()->after('display_name');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('font_family');
            $table->boolean('is_default')->default(false)->after('sort_order');
        });

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('fonts')
            ->whereIn('font_family', collect($this->defaultFonts())->pluck('font_family')->all())
            ->delete();

        Schema::table('fonts', function (Blueprint $table) {
            $table->dropColumn([
                'display_name',
                'google_family',
                'sort_order',
                'is_default',
            ]);
        });
    }
};
