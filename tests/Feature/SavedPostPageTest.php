<?php

namespace Tests\Feature;

use App\Models\Bookmarks;
use App\Models\Posts;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SavedPostPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('saved-post'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_see_only_their_saved_posts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $savedPost = $this->createVisiblePost('Current User Saved Website');
        $otherSavedPost = $this->createVisiblePost('Other User Saved Website');

        Bookmarks::factory()->create([
            'user_id' => $user->id,
            'post_id' => $savedPost->id,
        ]);
        Bookmarks::factory()->create([
            'user_id' => $otherUser->id,
            'post_id' => $otherSavedPost->id,
        ]);

        $this->actingAs($user)
            ->get(route('saved-post'))
            ->assertOk()
            ->assertSee('Saved Post')
            ->assertSee('Current User Saved Website')
            ->assertSee('data-saved-post-card', false)
            ->assertSee('Showing 1 of 1')
            ->assertDontSee('Other User Saved Website');
    }

    public function test_empty_saved_post_page_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('saved-post'))
            ->assertOk()
            ->assertSee('No saved posts yet')
            ->assertSee('Save posts from the card action menu and they will appear here.')
            ->assertSee('Browse posts')
            ->assertSee('data-saved-post-empty', false);
    }

    public function test_saved_post_page_renders_filter_controls_and_count(): void
    {
        $user = User::factory()->create();
        $post = $this->createVisiblePost('Filterable Saved Website');

        Bookmarks::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->get(route('saved-post', ['search' => 'Filterable', 'sort' => 'az']))
            ->assertOk()
            ->assertSee('data-saved-post-filter-form', false)
            ->assertSee('data-saved-post-search', false)
            ->assertSee('data-saved-post-sort', false)
            ->assertSee('data-saved-post-start-date', false)
            ->assertSee('data-saved-post-end-date', false)
            ->assertSee('value="Filterable"', false)
            ->assertSee('A-Z')
            ->assertSee('Showing 1 of 1');
    }

    public function test_users_cannot_save_their_own_posts(): void
    {
        $user = User::factory()->create();
        $post = Posts::factory()->create(['title' => 'Own Website']);

        UserPosts::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('posts.bookmark', $post))
            ->assertForbidden();

        $this->assertDatabaseMissing((new Bookmarks)->getTable(), [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_users_can_unsave_from_the_saved_post_page(): void
    {
        $user = User::factory()->create();
        $post = $this->createVisiblePost('Remove Saved Website');

        Bookmarks::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->from(route('saved-post'))
            ->post(route('posts.bookmark', $post))
            ->assertRedirect(route('saved-post'))
            ->assertSessionHas('success', 'Post removed from saved posts.');

        $this->assertDatabaseMissing((new Bookmarks)->getTable(), [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->get(route('saved-post'))
            ->assertOk()
            ->assertDontSee('Remove Saved Website')
            ->assertSee('No saved posts yet');
    }

    private function createVisiblePost(string $title): Posts
    {
        $reviewer = User::factory()->create();
        $reviewer->settings()->update(['user_post_visible' => true]);
        $post = Posts::factory()->create([
            'title' => $title,
            'url' => 'https://'.str($title)->slug().'.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $reviewer->id,
            'description' => "{$title} description.",
        ]);

        return $post;
    }
}
