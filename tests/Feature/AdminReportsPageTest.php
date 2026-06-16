<?php

namespace Tests\Feature;

use App\Models\Comments;
use App\Models\Notificatioins;
use App\Models\Posts;
use App\Models\Reports;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminReportsPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('reports'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_receive_forbidden_response(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('reports'))
            ->assertForbidden();
    }

    public function test_admin_sees_reports_console_filters_post_tab_table_and_active_menu_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $report = $this->createPostReport('Console Target Post', 'Spam / Misleading');

        $response = $this->actingAs($admin)
            ->get(route('reports'))
            ->assertOk();

        $html = $response->getContent();
        $this->assertTrue(
            str_contains($html, '/build/assets/reports-') || str_contains($html, 'reports.css'),
            'Failed asserting that reports CSS is loaded.'
        );

        $response->assertSee('data-admin-reports-page', false)
            ->assertSee('data-report-filter-form', false)
            ->assertSee('data-report-search', false)
            ->assertSee('data-report-status-filter', false)
            ->assertSee('data-report-date-filter', false)
            ->assertSee('data-report-tab="posts"', false)
            ->assertSee('data-report-posts-panel', false)
            ->assertSee('Post Audit Queue')
            ->assertSee($report->post->title)
            ->assertSee($report->reason)
            ->assertSee('href="'.route('reports').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_unread_reports_render_ping_indicator_and_read_reports_do_not(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $unreadReport = $this->createPostReport('Unread Ping Post', 'Scams / Fraud');
        $readReport = $this->createPostReport('Read Quiet Post', 'Fake / False Info', true);

        $this->actingAs($admin)
            ->get(route('reports', ['status' => 'all']))
            ->assertOk()
            ->assertSee('Unread Ping Post')
            ->assertSee('Read Quiet Post')
            ->assertSee('animate-ping', false)
            ->assertSee('unread-state', false)
            ->assertSee('read-state', false);

        $this->actingAs($admin)
            ->get(route('reports', ['status' => 'read']))
            ->assertOk()
            ->assertSee($readReport->post->title)
            ->assertDontSee($unreadReport->post->title)
            ->assertDontSee('animate-ping', false);
    }

    public function test_status_search_and_reported_date_filters_work(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matchingReport = $this->createPostReport(
            title: 'Filtered Security Post',
            reason: 'Unique Search Reason',
            read: false,
            createdAt: Carbon::parse('2026-05-20 09:00:00'),
        );
        $otherReport = $this->createPostReport(
            title: 'Other Report Post',
            reason: 'Other Reason',
            read: true,
            createdAt: Carbon::parse('2026-05-21 09:00:00'),
        );

        $this->actingAs($admin)
            ->get(route('reports', ['search' => 'Unique Search Reason', 'status' => 'all']))
            ->assertOk()
            ->assertSee($matchingReport->post->title)
            ->assertDontSee($otherReport->post->title);

        $this->actingAs($admin)
            ->get(route('reports', ['status' => 'read']))
            ->assertOk()
            ->assertSee($otherReport->post->title)
            ->assertDontSee($matchingReport->post->title);

        $this->actingAs($admin)
            ->get(route('reports', ['reported_date' => '2026-05-20', 'status' => 'all']))
            ->assertOk()
            ->assertSee($matchingReport->post->title)
            ->assertDontSee($otherReport->post->title);
    }

    public function test_admin_can_mark_report_as_read(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $report = $this->createPostReport('Mark Read Post', 'Harassment / Abuse');

        $this->actingAs($admin)
            ->withoutMiddleware()
            ->from(route('reports'))
            ->patch(route('reports.read', $report))
            ->assertRedirect(route('reports'))
            ->assertSessionHas('success', 'Report marked as read.');

        $this->assertTrue($report->fresh()->admin_read);
    }

    public function test_non_admin_users_cannot_mark_reports_read(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $report = $this->createPostReport('Forbidden Mark Read Post', 'Illegal Activities');

        $this->actingAs($user)
            ->withoutMiddleware()
            ->patch(route('reports.read', $report))
            ->assertForbidden();

        $this->assertFalse($report->fresh()->admin_read);
    }

    public function test_comment_and_user_tabs_are_enabled_and_functional(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $commentReport = $this->createCommentReport('Malicious link comment content', 'Spam Link');
        $userReport = $this->createUserReport('FlaggedUserSB', 'Harassment');

        $this->actingAs($admin)
            ->get(route('reports'))
            ->assertOk()
            ->assertSee('data-report-tab="comments"', false)
            ->assertSee('data-report-tab="users"', false)
            ->assertDontSee('COMMENT <span>Coming soon</span>', false)
            ->assertDontSee('USER <span>Coming soon</span>', false)
            ->assertSee('Comment reports')
            ->assertSee('User reports')
            ->assertSee('Malicious link comment content')
            ->assertSee('FlaggedUserSB')
            ->assertSee('Spam Link')
            ->assertSee('Harassment');
    }

    public function test_opening_report_notification_marks_notification_read_but_not_report_read(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create(['role' => 'user']);
        $report = $this->createPostReport('Notification Routed Post', 'Spam / Misleading', reporter: $reporter);
        $notification = Notificatioins::factory()->create([
            'to_user_id' => $admin->id,
            'from_user_id' => $reporter->id,
            'target_type' => 'posts',
            'target_id' => $report->target_id,
            'message' => "{$reporter->name} reported post: {$report->post->title}",
            'is_read' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="1 unread notifications"', false)
            ->assertSee('action="'.route('notifications.open', $notification).'"', false);

        $this->actingAs($admin)
            ->withoutMiddleware()
            ->post(route('notifications.open', $notification))
            ->assertRedirect(route('reports', ['report' => $report->target_id]));

        $this->assertTrue($notification->fresh()->is_read);
        $this->assertFalse($report->fresh()->admin_read);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="Notifications"', false)
            ->assertDontSee('noti-badge', false);
    }

    private function createPostReport(
        string $title,
        string $reason,
        bool $read = false,
        ?Carbon $createdAt = null,
        ?User $reporter = null,
    ): Reports {
        $reporter ??= User::factory()->create(['role' => 'user']);
        $post = Posts::factory()->create(['title' => $title]);

        return Reports::query()->create([
            'user_id' => $reporter->id,
            'target_name' => 'posts',
            'target_id' => $post->id,
            'reason' => $reason,
            'admin_read' => $read,
            'created_at' => $createdAt ?? now(),
            'updated_at' => $createdAt ?? now(),
        ])->load(['post', 'reporter']);
    }

    private function createCommentReport(
        string $content,
        string $reason,
        bool $read = false,
        ?Carbon $createdAt = null,
        ?User $reporter = null,
    ): Reports {
        $reporter ??= User::factory()->create(['role' => 'user']);
        $post = Posts::factory()->create();
        $comment = Comments::factory()->create([
            'post_id' => $post->id,
            'content' => $content,
        ]);

        return Reports::query()->create([
            'user_id' => $reporter->id,
            'target_name' => 'comments',
            'target_id' => $comment->id,
            'reason' => $reason,
            'admin_read' => $read,
            'created_at' => $createdAt ?? now(),
            'updated_at' => $createdAt ?? now(),
        ])->load(['comment', 'reporter']);
    }

    private function createUserReport(
        string $targetName,
        string $reason,
        bool $read = false,
        ?Carbon $createdAt = null,
        ?User $reporter = null,
    ): Reports {
        $reporter ??= User::factory()->create(['role' => 'user']);
        $targetUser = User::factory()->create(['name' => $targetName]);

        return Reports::query()->create([
            'user_id' => $reporter->id,
            'target_name' => 'users',
            'target_id' => $targetUser->id,
            'reason' => $reason,
            'admin_read' => $read,
            'created_at' => $createdAt ?? now(),
            'updated_at' => $createdAt ?? now(),
        ])->load(['targetUser', 'reporter']);
    }
}
