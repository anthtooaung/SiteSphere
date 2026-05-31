<?php

namespace Tests\Feature;

use Database\Seeders\TagsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryTagSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_and_tag_seed_data_is_imported_from_text_file(): void
    {
        $this->seed(TagsSeeder::class);

        $this->assertDatabaseHas('categories', [
            'name' => 'Programming & Development',
            'slug' => 'programming-development',
        ]);

        $programmingCategoryId = DB::table('categories')
            ->where('slug', 'programming-development')
            ->value('id');
        $frontendTagId = DB::table('tags')
            ->where('slug', 'frontend')
            ->value('id');

        $this->assertDatabaseHas('tags', [
            'name' => 'frontend',
            'slug' => 'frontend',
            'tag_color' => TagsSeeder::DEFAULT_TAG_COLOR,
        ]);

        $this->assertDatabaseHas('category_tags', [
            'category_id' => $programmingCategoryId,
            'tag_id' => $frontendTagId,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Special Tags',
            'slug' => 'special-tags',
        ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'enterprise',
            'slug' => 'enterprise',
            'tag_color' => '#374151',
        ]);
    }

    public function test_category_and_tag_seed_data_is_idempotent(): void
    {
        $this->seed(TagsSeeder::class);
        $this->seed(TagsSeeder::class);

        $programmingCategoryId = DB::table('categories')
            ->where('slug', 'programming-development')
            ->value('id');
        $frontendTagId = DB::table('tags')
            ->where('slug', 'frontend')
            ->value('id');

        $this->assertSame(1, DB::table('tags')->where('slug', 'frontend')->count());
        $this->assertSame(
            1,
            DB::table('category_tags')
                ->where('category_id', $programmingCategoryId)
                ->where('tag_id', $frontendTagId)
                ->count(),
        );
    }
}
