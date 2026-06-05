<?php

namespace Tests\Feature;

use App\Models\Comments;
use App\Models\Posts;
use App\Models\User;
use Database\Seeders\FontsSeeder;
use Database\Seeders\ThemesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
        $this->seed(ThemesSeeder::class);
    }

    public function test_post_detail_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $post = Posts::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('posts.show', $post->slug));

        $response->assertOk();
        $response->assertSee($post->title);
    }

    public function test_user_can_submit_rating_and_comment(): void
    {
        $user = User::factory()->create();
        $post = Posts::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('posts.show', $post->slug))
            ->post(route('posts.comments.store', $post->slug), [
                'rating' => 4,
                'content' => 'This is a fantastic website with clean layout.',
            ]);

        $response->assertRedirect(route('posts.show', $post->slug));

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => 'This is a fantastic website with clean layout.',
        ]);

        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'rating' => 4,
        ]);
    }

    public function test_user_can_toggle_helpful_reaction(): void
    {
        $user = User::factory()->create();
        $post = Posts::factory()->create();

        $comment = Comments::create([
            'user_id' => User::factory()->create()->id,
            'post_id' => $post->id,
            'content' => 'Existing review content.',
        ]);

        // Toggle helpful ON
        $response = $this
            ->actingAs($user)
            ->postJson(route('comments.react', $comment->id));

        $response->assertOk();
        $response->assertJson([
            'voted' => true,
            'helpful_count' => 1,
        ]);

        $this->assertDatabaseHas('comment_reactions', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'helpful' => true,
        ]);

        // Toggle helpful OFF
        $response = $this
            ->actingAs($user)
            ->postJson(route('comments.react', $comment->id));

        $response->assertOk();
        $response->assertJson([
            'voted' => false,
            'helpful_count' => 0,
        ]);

        $this->assertDatabaseMissing('comment_reactions', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
        ]);
    }
}
