<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Posts;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UploadPostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_from_upload_post_routes(): void
    {
        $this->get(route('posts.create'))->assertRedirect(route('login'));

        $this->post(route('posts.store'), [])->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_upload_page_with_database_categories_and_tags(): void
    {
        $user = User::factory()->create();
        $category = Categories::factory()->create([
            'name' => 'Developer Tools',
            'slug' => 'developer-tools',
        ]);
        $tag = Tags::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
            'tag_color' => '#14b8a6',
        ]);

        DB::table('category_tags')->insert([
            'category_id' => $category->id,
            'tag_id' => $tag->id,
        ]);

        $response = $this->actingAs($user)->get(route('posts.create'));

        $response
            ->assertOk()
            ->assertViewIs('layout.upload-post')
            ->assertSee('Create Post')
            ->assertSee('Developer Tools')
            ->assertSee('Laravel')
            ->assertSee('id="preview-wrapper-column"', false)
            ->assertSee('data-upload-preview-card', false)
            ->assertDontSee('cdn.tailwindcss.com')
            ->assertDontSee('cdnjs.cloudflare.com/ajax/libs/font-awesome');
    }

    public function test_authenticated_users_can_store_a_post_with_tags(): void
    {
        $user = User::factory()->create();
        $tag = Tags::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'title' => 'Useful Laravel Package',
            'url' => 'https://package-example.test',
            'description' => 'A strong description of why this package is useful.',
            'tags' => [$tag->id],
        ]);

        $response
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Post created successfully.');

        $post = Posts::query()->where('url', 'https://package-example.test')->firstOrFail();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Useful Laravel Package',
            'url' => 'https://package-example.test',
        ]);
        $this->assertDatabaseHas('user_posts', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'description' => 'A strong description of why this package is useful.',
        ]);
        $this->assertDatabaseHas('post_tags', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_upload_post_validates_url_and_tags(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('posts.create'))
            ->post(route('posts.store'), [
                'title' => 'Invalid Upload',
                'url' => 'not-a-url',
                'description' => 'This should fail validation.',
                'tags' => [],
            ]);

        $response
            ->assertRedirect(route('posts.create'))
            ->assertSessionHasErrors(['url', 'tags']);

        $this->assertDatabaseMissing('posts', [
            'title' => 'Invalid Upload',
        ]);
    }

    public function test_same_user_cannot_review_the_same_url_twice(): void
    {
        $user = User::factory()->create();
        $post = Posts::factory()->create([
            'url' => 'https://duplicate-example.test',
        ]);
        $tag = Tags::factory()->create();

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('posts.create'))
            ->post(route('posts.store'), [
                'title' => 'Duplicate Review',
                'url' => 'https://duplicate-example.test',
                'description' => 'Trying to review the same URL twice.',
                'tags' => [$tag->id],
            ]);

        $response
            ->assertRedirect(route('posts.create'))
            ->assertSessionHasErrors(['url']);
    }
}
