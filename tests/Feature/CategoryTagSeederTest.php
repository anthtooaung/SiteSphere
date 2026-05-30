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

        $this->assertDatabaseHas('tags', [
            'category_id' => $programmingCategoryId,
            'name' => 'frontend',
            'slug' => 'frontend',
            'tag_color' => TagsSeeder::DEFAULT_TAG_COLOR,
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
}
