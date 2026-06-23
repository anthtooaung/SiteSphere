<?php

namespace Tests\Feature;

use App\Models\AuditLogs;
use App\Models\Bookmarks;
use App\Models\Notificatioins;
use App\Models\Posts;
use App\Models\Reports;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use SweetAlert2\Laravel\Swal;
use Tests\TestCase;

class PostCardActionsTest extends TestCase
{
    use LazilyRefreshDatabase;

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
            ->assertDontSee('data-post-card-report-form', false)
            ->assertDontSee('data-post-card-action="ban"', false)
            ->assertDontSee('Ban');
    }

    public function test_authenticated_home_cards_render_report_modal_form(): void
    {
        $user = User::factory()->create();
        $post = $this->createVisiblePost('Report Modal Post');

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('data-post-card-report-trigger', false)
            ->assertSee('data-post-card-report-modal', false)
            ->assertSee('data-post-card-report-form', false)
            ->assertSee('data-post-card-report-reason', false)
            ->assertSee('data-post-card-report-details', false)
            ->assertSee('data-post-card-report-submit', false)
            ->assertSee('x-bind:disabled="! reportReason"', false)
            ->assertSee('Spam / Misleading')
            ->assertSee('Harassment / Abuse')
            ->assertSee('Scams / Fraud')
            ->assertSee('Describe context or reasons to help us review this report faster.');
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
                'reason' => 'Spam / Misleading',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Post reported.')
            ->assertSessionHas(Swal::SESSION_KEY, function (array $toast): bool {
                return $toast['toast'] === true
                    && $toast['position'] === 'top-end'
                    && $toast['showConfirmButton'] === false
                    && $toast['icon'] === 'success'
                    && $toast['title'] === 'Report submitted'
                    && $toast['text'] === 'Thanks for helping us keep SiteSphere safe.';
            });

        $this->assertDatabaseHas((new Reports)->getTable(), [
            'user_id' => $user->id,
            'target_name' => 'posts',
            'target_id' => $post->id,
            'reason' => 'Spam / Misleading',
            'admin_read' => false,
        ]);
    }

    public function test_reporting_a_post_notifies_every_admin(): void
    {
        $reporter = User::factory()->create(['name' => 'Reporting User']);
        $firstAdmin = User::factory()->create(['role' => 'admin']);
        $secondAdmin = User::factory()->create(['role' => 'admin']);
        $regularUser = User::factory()->create(['role' => 'user']);
        $post = $this->createVisiblePost('Admin Notification Target');
        $message = 'Reporting User reported post: Admin Notification Target';

        $this->actingAs($reporter)
            ->from(route('home'))
            ->post(route('posts.report', $post), [
                'reason' => 'Spam / Misleading',
            ])
            ->assertRedirect(route('home'));

        foreach ([$firstAdmin, $secondAdmin] as $admin) {
            $this->assertDatabaseHas((new Notificatioins)->getTable(), [
                'to_user_id' => $admin->id,
                'from_user_id' => $reporter->id,
                'target_type' => 'posts',
                'target_id' => $post->id,
                'message' => $message,
                'is_read' => false,
            ]);
        }

        $this->assertDatabaseMissing((new Notificatioins)->getTable(), [
            'to_user_id' => $regularUser->id,
            'message' => $message,
        ]);

        $this->assertSame(2, Notificatioins::query()->where('message', $message)->count());
    }

    public function test_reporting_a_post_succeeds_without_admin_users(): void
    {
        $reporter = User::factory()->create(['role' => 'user']);
        $post = $this->createVisiblePost('No Admin Report Target');

        $this->actingAs($reporter)
            ->from(route('home'))
            ->post(route('posts.report', $post), [
                'reason' => 'Fake / False Info',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Post reported.');

        $this->assertDatabaseHas((new Reports)->getTable(), [
            'user_id' => $reporter->id,
            'target_name' => 'posts',
            'target_id' => $post->id,
            'reason' => 'Fake / False Info',
            'admin_read' => false,
        ]);

        $this->assertDatabaseCount((new Notificatioins)->getTable(), 0);
    }

    public function test_admin_home_notification_box_shows_report_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create(['name' => 'Dropdown Reporter']);
        $post = $this->createVisiblePost('Dropdown Report Target');
        $message = 'Dropdown Reporter reported post: Dropdown Report Target';

        $this->actingAs($reporter)
            ->from(route('home'))
            ->post(route('posts.report', $post), [
                'reason' => 'Illegal Activities',
            ])
            ->assertRedirect(route('home'));

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="1 unread notifications"', false)
            ->assertSee('<span class="noti-badge">1</span>', false)
            ->assertSee($message);
    }

    public function test_authenticated_users_can_report_a_post_with_details(): void
    {
        $user = User::factory()->create();
        $post = $this->createVisiblePost('Detailed Reported Post');

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('posts.report', $post), [
                'reason' => 'Scams / Fraud',
                'details' => 'This post links to a suspicious checkout flow.',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Post reported.');

        $this->assertDatabaseHas((new Reports)->getTable(), [
            'user_id' => $user->id,
            'target_name' => 'posts',
            'target_id' => $post->id,
            'reason' => "Scams / Fraud\n\nDetails: This post links to a suspicious checkout flow.",
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
            ->assertSessionHas('success', 'Post banned and soft deleted.');

        $this->assertDatabaseHas((new UserPosts)->getTable(), [
            'post_id' => $post->id,
            'user_hidden' => true,
        ]);

        $this->assertDatabaseHas((new AuditLogs)->getTable(), [
            'user_id' => $admin->id,
            'action' => 'ban_post',
            'target_type' => Posts::class,
            'target_id' => $post->id,
            'reason' => 'Post soft deleted and all descriptions hidden by an admin.',
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
