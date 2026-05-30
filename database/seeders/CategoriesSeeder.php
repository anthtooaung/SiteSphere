<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public const DATA_FILE = __DIR__.'/data/tags-and-categories.txt';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (array_keys(self::categoryTagsFromFile()) as $categoryName) {
            $categorySlug = Str::slug($categoryName);

            DB::table('categories')->updateOrInsert(
                ['slug' => $categorySlug],
                [
                    'name' => $categoryName,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function categoryTagsFromFile(): array
    {
        $categories = [];
        $currentCategory = null;

        foreach (file(self::DATA_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (preg_match('/^\d+\.\s+(.+)$/', $line, $matches) === 1) {
                $currentCategory = trim($matches[1]);
                $categories[$currentCategory] = [];

                continue;
            }

            if ($currentCategory && preg_match('/^\s+\d+\.\s+(.+)$/', $line, $matches) === 1) {
                $categories[$currentCategory][] = trim($matches[1]);
            }
        }

        return $categories;
    }
}
