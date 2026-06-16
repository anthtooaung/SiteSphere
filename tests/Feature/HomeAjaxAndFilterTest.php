<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomeAjaxAndFilterTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FontsSeeder::class);
    }

    public function test_home_handles_ajax_requests_returning_json(): void
    {
        $user = User::factory()->create();
        Posts::factory()->count(5)->create()->each(function ($post) use ($user) {
            UserPosts::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);
        });

        $response = $this->get('/home', ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'html',
                'hasMorePages',
                'total',
                'currentPage',
            ]);
    }

    public function test_home_can_filter_by_category(): void
    {
        $user = User::factory()->create();
        $category1 = Categories::factory()->create(['name' => 'Cat 1', 'slug' => 'cat-1']);
        $category2 = Categories::factory()->create(['name' => 'Cat 2', 'slug' => 'cat-2']);

        $tag1 = Tags::factory()->create();
        $tag2 = Tags::factory()->create();

        $category1->tags()->attach($tag1);
        $category2->tags()->attach($tag2);

        $post1 = Posts::factory()->create(['title' => 'Post in Cat 1']);
        $post1->tags()->attach($tag1);
        UserPosts::factory()->create(['post_id' => $post1->id, 'user_id' => $user->id]);

        $post2 = Posts::factory()->create(['title' => 'Post in Cat 2']);
        $post2->tags()->attach($tag2);
        UserPosts::factory()->create(['post_id' => $post2->id, 'user_id' => $user->id]);

        $response = $this->get('/home?category=cat-1');

        $response->assertOk()
            ->assertSee('Post in Cat 1')
            ->assertDontSee('Post in Cat 2');
    }

    public function test_home_can_filter_by_tags(): void
    {
        $user = User::factory()->create();
        $tag1 = Tags::factory()->create(['name' => 'Tag 1', 'slug' => 'tag-1']);
        $tag2 = Tags::factory()->create(['name' => 'Tag 2', 'slug' => 'tag-2']);

        $post1 = Posts::factory()->create(['title' => 'Post with Tag 1']);
        $post1->tags()->attach($tag1);
        UserPosts::factory()->create(['post_id' => $post1->id, 'user_id' => $user->id]);

        $post2 = Posts::factory()->create(['title' => 'Post with Tag 2']);
        $post2->tags()->attach($tag2);
        UserPosts::factory()->create(['post_id' => $post2->id, 'user_id' => $user->id]);

        $response = $this->get('/home?tags=tag-1');

        $response->assertOk()
            ->assertSee('Post with Tag 1')
            ->assertDontSee('Post with Tag 2');
    }

    public function test_home_can_filter_by_rating(): void
    {
        $user = User::factory()->create();

        $post1 = Posts::factory()->create(['title' => 'High Rated Post']);
        UserPosts::factory()->create(['post_id' => $post1->id, 'user_id' => $user->id]);
        Ratings::factory()->create(['post_id' => $post1->id, 'rating' => 5]);

        $post2 = Posts::factory()->create(['title' => 'Low Rated Post']);
        UserPosts::factory()->create(['post_id' => $post2->id, 'user_id' => $user->id]);
        Ratings::factory()->create(['post_id' => $post2->id, 'rating' => 2]);

        // Filter for rating >= 4
        $response = $this->get('/home?rating=4');

        $response->assertOk()
            ->assertSee('High Rated Post')
            ->assertDontSee('Low Rated Post');
    }

    public function test_home_can_sort_by_newest(): void
    {
        $user = User::factory()->create();

        $oldPost = Posts::factory()->create(['title' => 'Old Post', 'created_at' => now()->subDays(10)]);
        UserPosts::factory()->create(['post_id' => $oldPost->id, 'user_id' => $user->id]);

        $newPost = Posts::factory()->create(['title' => 'New Post', 'created_at' => now()]);
        UserPosts::factory()->create(['post_id' => $newPost->id, 'user_id' => $user->id]);

        $response = $this->get('/home?sort=newest');

        $response->assertOk()
            ->assertSeeInOrder(['New Post', 'Old Post']);
    }

    public function test_home_can_sort_by_rating(): void
    {
        $user = User::factory()->create();

        $lowRated = Posts::factory()->create(['title' => 'Low Rated']);
        UserPosts::factory()->create(['post_id' => $lowRated->id, 'user_id' => $user->id]);
        Ratings::factory()->create(['post_id' => $lowRated->id, 'rating' => 1]);

        $highRated = Posts::factory()->create(['title' => 'High Rated']);
        UserPosts::factory()->create(['post_id' => $highRated->id, 'user_id' => $user->id]);
        Ratings::factory()->create(['post_id' => $highRated->id, 'rating' => 5]);

        $response = $this->get('/home?sort=rating');

        $response->assertOk()
            ->assertSeeInOrder(['High Rated', 'Low Rated']);
    }

    public function test_home_can_filter_by_search_term(): void
    {
        $user = User::factory()->create();

        $post1 = Posts::factory()->create(['title' => 'Specific Keyword Post', 'url' => 'https://example.com']);
        UserPosts::factory()->create(['post_id' => $post1->id, 'user_id' => $user->id]);

        $post2 = Posts::factory()->create(['title' => 'Other Post', 'url' => 'https://other.com']);
        UserPosts::factory()->create(['post_id' => $post2->id, 'user_id' => $user->id]);

        // Search by title
        $response = $this->get('/home?search=Keyword');
        $response->assertOk()
            ->assertSee('Specific Keyword Post')
            ->assertDontSee('Other Post');

        // Search by URL
        $response = $this->get('/home?search=example');
        $response->assertOk()
            ->assertSee('Specific Keyword Post')
            ->assertDontSee('Other Post');
    }
}
