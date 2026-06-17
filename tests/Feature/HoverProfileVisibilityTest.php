<?php

namespace Tests\Feature;

use App\Models\Bookmarks;
use App\Models\Posts;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoverProfileVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_hover_profile_is_triggered_for_visible_users_on_home(): void
    {
        $viewer = User::factory()->create();
        $visibleReviewer = User::factory()->create(['name' => 'Visible User']);

        $post = Posts::factory()->create(['title' => 'Test Post']);

        UserPosts::query()->delete();
        UserPosts::query()->create([
            'post_id' => $post->id,
            'user_id' => $visibleReviewer->id,
            'description' => 'Test description',
            'user_hidden' => false,
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response->assertStatus(200);

        // Check for the user ID in the profiles data
        // Just check for the pattern that includes the ID
        $response->assertSee('user_id', false);
        $response->assertSee((string) $visibleReviewer->id, false);
        $response->assertSee('@visible_user', false);
    }

    public function test_hover_profile_is_not_triggered_for_hidden_users_on_home(): void
    {
        $viewer = User::factory()->create();
        $hiddenReviewer = User::factory()->create(['name' => 'Hidden User']);

        $post = Posts::factory()->create(['title' => 'Test Post']);

        UserPosts::query()->delete();
        UserPosts::query()->create([
            'post_id' => $post->id,
            'user_id' => $hiddenReviewer->id,
            'description' => 'Hidden description',
            'user_hidden' => true,
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response->assertStatus(200);

        // For the hidden user, we expect their ID NOT to be associated with user_id
        // We set user_id to null, so the string "user_id":ID should NOT be there.
        $content = $response->getContent();
        $this->assertFalse(str_contains($content, '"user_id":'.$hiddenReviewer->id));
        $this->assertFalse(str_contains($content, 'user_id&quot;:'.$hiddenReviewer->id));

        $response->assertSee('Anonymous');
        $response->assertDontSee('@hidden_user');
    }

    public function test_new_user_has_user_post_visible_true_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->settings->user_post_visible);
    }

    public function test_hover_profile_is_not_triggered_for_hidden_users_on_saved_posts(): void
    {
        $viewer = User::factory()->create();
        $hiddenReviewer = User::factory()->create(['name' => 'Hidden User']);

        $post = Posts::factory()->create(['title' => 'Saved Post']);

        UserPosts::query()->delete();
        UserPosts::query()->create([
            'post_id' => $post->id,
            'user_id' => $hiddenReviewer->id,
            'description' => 'Hidden description',
            'user_hidden' => true,
        ]);

        Bookmarks::factory()->create([
            'user_id' => $viewer->id,
            'post_id' => $post->id,
        ]);

        $response = $this->actingAs($viewer)->get('/saved-posts');

        $response->assertStatus(200);

        $content = $response->getContent();

        $this->assertFalse(str_contains($content, '"user_id":'.$hiddenReviewer->id));
        $this->assertFalse(str_contains($content, 'user_id&quot;:'.$hiddenReviewer->id));

        $response->assertSee('Anonymous');
    }
}
