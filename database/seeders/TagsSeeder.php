<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagsSeeder extends Seeder
{
    public const DEFAULT_TAG_COLOR = '#374151';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(CategoriesSeeder::class);

        foreach (CategoriesSeeder::categoryTagsFromFile() as $categoryName => $tagNames) {
            $categoryId = DB::table('categories')
                ->where('slug', Str::slug($categoryName))
                ->value('id');

            foreach ($tagNames as $tagName) {
                DB::table('tags')->updateOrInsert(
                    [
                        'category_id' => $categoryId,
                        'slug' => Str::slug($tagName),
                    ],
                    [
                        'name' => $tagName,
                        'tag_color' => self::DEFAULT_TAG_COLOR,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        }
    }
}
