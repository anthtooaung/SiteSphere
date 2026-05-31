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
            $now = now();
            $categoryId = DB::table('categories')
                ->where('slug', Str::slug($categoryName))
                ->value('id');

            foreach ($tagNames as $tagName) {
                DB::table('tags')->updateOrInsert(
                    [
                        'slug' => Str::slug($tagName),
                    ],
                    [
                        'name' => $tagName,
                        'tag_color' => self::DEFAULT_TAG_COLOR,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );

                $tagId = DB::table('tags')
                    ->where('slug', Str::slug($tagName))
                    ->value('id');

                DB::table('category_tags')->updateOrInsert(
                    [
                        'category_id' => $categoryId,
                        'tag_id' => $tagId,
                    ],
                    [
                        'category_id' => $categoryId,
                        'tag_id' => $tagId,
                    ],
                );
            }
        }
    }
}
