<?php

namespace Tests\Feature;

use App\Models\AuditLogs;
use App\Models\Bookmarks;
use App\Models\Posts;
use App\Models\Reports;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCardActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guest_home_cards_render_auth_gated_action_menu(): void
    {
        $post = $this->createVisiblePost('Guest Action Menu Post');

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('data-post-card-actions-button', false)
            ->assertSee('data-post-card-actions-menu', false)
            ->assertSee('data-auth-required="bookmark"', false)
            ->assertSee('data-auth-required="report"', false)
            ->assertSee('Save Post')
            ->assertSee('Report')
            ->assertDontSee('data-post-card-action="ban"', false)
            ->assertDontSee('Ban');
    }

    public function test_authenticated_users_can_save_and_unsave_a_post(): void
    {
        $user = User::factory()->create();
        $post = $this->createVisiblePost('Bookmark Toggle Post');

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('posts.bookmark', $post))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Post saved.');

        $this->assertDatabaseHas((new Bookmarks)->getTable(), [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Unsave Post');

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('posts.bookmark', $post))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Post removed from saved posts.');

        $this->assertDatabaseMissing((new Bookmarks)->getTable(), [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_authenticated_users_can_report_a_post(): void
    {
        $user = User::factory()->create();
        $post = $this->createVisiblePost('Reported Post');

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('posts.report', $post), [
                'reason' => 'Reported from the post card action menu.',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Post reported.');

        $this->assertDatabaseHas((new Reports)->getTable(), [
            'user_id' => $user->id,
            'target_name' => 'posts',
            'target_id' => $post->id,
            'reason' => 'Reported from the post card action menu.',
            'admin_read' => false,
        ]);
    }

    public function test_regular_users_cannot_ban_posts_and_do_not_see_ban_action(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $post = $this->createVisiblePost('Regular User Cannot Ban Post');

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertDontSee('data-post-card-action="ban"', false)
            ->assertDontSee('>Ban</span>', false);

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('posts.ban', $post))
            ->assertForbidden();

        $this->assertDatabaseHas((new UserPosts)->getTable(), [
            'post_id' => $post->id,
            'user_hidden' => false,
        ]);
    }

    public function test_admin_users_can_ban_posts_from_the_home_feed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = $this->createVisiblePost('Admin Ban Target Post');

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('data-post-card-action="ban"', false)
            ->assertSee('Ban');

        $this->actingAs($admin)
            ->from(route('home'))
            ->post(route('posts.ban', $post))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Post banned.');

        $this->assertDatabaseHas((new UserPosts)->getTable(), [
            'post_id' => $post->id,
            'user_hidden' => true,
        ]);

        $this->assertDatabaseHas((new AuditLogs)->getTable(), [
            'user_id' => $admin->id,
            'action' => 'ban_post',
            'target_type' => Posts::class,
            'target_id' => $post->id,
            'reason' => 'Post hidden from the home feed by an admin.',
        ]);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee($post->title);
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
