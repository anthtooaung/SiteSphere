<?php

namespace Tests\Feature;

use App\Models\Comments;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Database\Seeders\ThemesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostDetailTest extends TestCase
{
    use LazilyRefreshDatabase;

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

    public function test_contributors_can_be_anonymous_on_post_detail_page(): void
    {
        $viewer = User::factory()->create();
        $post = Posts::factory()->create();

        // 1. Visible contributor
        $visibleUser = User::factory()->create(['name' => 'Visible Contributor']);
        $visibleUser->settings()->update(['user_post_visible' => true]);
        UserPosts::create([
            'user_id' => $visibleUser->id,
            'post_id' => $post->id,
            'description' => 'Visible contribution text',
            'user_hidden' => false,
        ]);

        // 2. Anonymous contributor
        $anonymousUser = User::factory()->create(['name' => 'Secret Contributor']);
        $anonymousUser->settings()->update(['user_post_visible' => false]);
        UserPosts::create([
            'user_id' => $anonymousUser->id,
            'post_id' => $post->id,
            'description' => 'Secret contribution text',
            'user_hidden' => true,
        ]);

        $response = $this
            ->actingAs($viewer)
            ->get(route('posts.show', $post->slug));

        $response->assertOk();
        // The visible contributor name should be shown
        $response->assertSee('Visible Contributor');
        // The secret contributor name should NOT be shown
        $response->assertDontSee('Secret Contributor');
        // The anonymous placeholder should be shown
        $response->assertSee('Anonymous');
    }

    public function test_post_detail_page_displays_custom_user_tag_overrides(): void
    {
        $user = User::factory()->create();
        $post = Posts::factory()->create();
        $tag = Tags::factory()->create(['name' => 'Original Tag Name', 'tag_color' => '#222222']);
        $post->tags()->attach($tag->id);

        $tag->customTags()->create([
            'user_id' => $user->id,
            'name' => 'Customized Detail Tag',
            'color' => '#00FF99',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('posts.show', $post->slug));

        $response->assertOk();
        $response->assertSee('Customized Detail Tag');
        $response->assertSee('#00FF99');
        $response->assertDontSee('Original Tag Name');
    }

    public function test_post_detail_queries_stay_bounded_with_many_comments_and_ratings(): void
    {
        $viewer = User::factory()->create();
        $post = Posts::factory()->create([
            'title' => 'Query Bound Post',
        ]);
        $tag = Tags::factory()->create();
        $post->tags()->attach($tag->id);

        for ($index = 1; $index <= 6; $index++) {
            $reviewer = User::factory()->create();
            $reviewer->settings()->update(['user_post_visible' => true]);

            $reviewedPost = $index <= 5 ? $post : Posts::factory()->create();

            UserPosts::factory()->create([
                'user_id' => $reviewer->id,
                'post_id' => $reviewedPost->id,
                'description' => 'Query bound review '.$index,
            ]);

            Ratings::factory()->create([
                'user_id' => $reviewer->id,
                'post_id' => $reviewedPost->id,
                'rating' => ($index % 5) + 1,
            ]);
        }

        for ($index = 1; $index <= 10; $index++) {
            Comments::factory()->create([
                'user_id' => User::factory()->create()->id,
                'post_id' => $post->id,
                'content' => 'Query bound comment '.$index,
            ]);
        }

        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount): void {
            if (
                str_contains($query->sql, 'from "posts"')
                || str_contains($query->sql, 'from `posts`')
                || str_contains($query->sql, 'from "ratings"')
                || str_contains($query->sql, 'from `ratings`')
                || str_contains($query->sql, 'from "comments"')
                || str_contains($query->sql, 'from `comments`')
                || str_contains($query->sql, 'from "user_posts"')
                || str_contains($query->sql, 'from `user_posts`')
            ) {
                $queryCount++;
            }
        });

        $this->actingAs($viewer)
            ->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('Query Bound Post')
            ->assertSee('User Comments');

        $this->assertLessThanOrEqual(12, $queryCount);
    }

    public function test_user_can_edit_own_comment(): void
    {
        $user = User::factory()->create();
        $post = Posts::factory()->create();
        $comment = Comments::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => 'Old comment content.',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('comments.update', $comment->id), [
                'content' => 'Updated comment content.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment content.',
        ]);
    }

    public function test_user_cannot_edit_other_user_comment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Posts::factory()->create();
        $comment = Comments::create([
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
            'content' => 'Original content.',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('comments.update', $comment->id), [
                'content' => 'Unauthorized update attempt.',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Original content.',
        ]);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $post = Posts::factory()->create();
        $comment = Comments::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => 'To be deleted.',
        ]);
        Ratings::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'rating' => 4,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('comments.destroy', $comment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
        $this->assertDatabaseMissing('ratings', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_user_cannot_delete_other_user_comment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Posts::factory()->create();
        $comment = Comments::create([
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
            'content' => 'Safe comment.',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('comments.destroy', $comment->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherUser = User::factory()->create();
        $post = Posts::factory()->create();
        $comment = Comments::create([
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
            'content' => 'Admin delete target.',
        ]);
        Ratings::create([
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
            'rating' => 4,
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('comments.destroy', $comment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
        $this->assertDatabaseMissing('ratings', [
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_admin_can_ban_comment_which_soft_deletes_and_audit_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherUser = User::factory()->create();
        $post = Posts::factory()->create();
        $comment = Comments::create([
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
            'content' => 'To be banned.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('comments.ban', $comment->id), [
                'reason' => 'Inappropriate language.',
            ]);

        $response->assertRedirect();
        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'ban_comment',
            'category' => 'moderation',
            'target_type' => Comments::class,
            'target_id' => $comment->id,
            'reason' => 'Inappropriate language.',
        ]);

        $this->assertDatabaseHas('notificatioins', [
            'to_user_id' => $comment->user_id,
            'from_user_id' => $admin->id,
            'target_type' => 'comments',
            'target_id' => $comment->id,
            'message' => 'Your comment was banned by an admin. Reason: Inappropriate language.',
        ]);
    }

    public function test_non_admin_cannot_ban_comment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Posts::factory()->create();
        $comment = Comments::create([
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
            'content' => 'Cannot be banned by user.',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('comments.ban', $comment->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
        ]);
        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'ban_comment',
            'target_id' => $comment->id,
        ]);
    }

    public function test_user_can_report_comment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Posts::factory()->create();
        $comment = Comments::create([
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
            'content' => 'Reportable content.',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('comments.report', $comment->id), [
                'reason' => 'Spam / Misleading',
                'details' => 'This looks like advertisement.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reports', [
            'user_id' => $user->id,
            'target_name' => 'comments',
            'target_id' => $comment->id,
            'reason' => "Spam / Misleading\n\nDetails: This looks like advertisement.",
        ]);
    }
}
