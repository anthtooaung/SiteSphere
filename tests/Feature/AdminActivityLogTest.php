<?php

namespace Tests\Feature;

use App\Models\AuditLogs;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminActivityLogTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.activity-log'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.activity-date', ['date' => '2026-05-20']))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_receive_forbidden_response(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.activity-log'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.activity-date', ['date' => '2026-05-20']))
            ->assertForbidden();
    }

    public function test_admin_can_access_activity_log_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.activity-log'))
            ->assertOk()
            ->assertViewIs('layout.menu.activity-log');
    }

    public function test_admin_can_fetch_activity_for_specific_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $date = '2026-05-20';

        AuditLogs::factory()->create([
            'user_id' => $admin->id,
            'created_at' => Carbon::parse($date),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.activity-date', ['date' => $date]))
            ->assertOk()
            ->assertViewIs('partials.admin-activity-card');
    }

    public function test_admin_can_fetch_all_activity_for_specific_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $date = '2026-05-20';

        // Create 5 logs
        AuditLogs::factory()->count(5)->create([
            'user_id' => $admin->id,
            'created_at' => Carbon::parse($date),
        ]);

        // Default should take only 3
        $this->actingAs($admin)
            ->get(route('admin.activity-date', ['date' => $date]))
            ->assertOk()
            ->assertViewHas('logs', fn($logs) => $logs->count() === 3)
            ->assertViewHas('totalCount', 5)
            ->assertViewHas('isFullList', false);

        // With ?all=true should take all 5
        $this->actingAs($admin)
            ->get(route('admin.activity-date', ['date' => $date, 'all' => 'true']))
            ->assertOk()
            ->assertViewHas('logs', fn($logs) => $logs->count() === 5)
            ->assertViewHas('totalCount', 5)
            ->assertViewHas('isFullList', true);
    }

    public function test_admin_can_fetch_activity_for_date_with_no_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $date = '2026-05-20';

        // No logs created for this date

        $this->actingAs($admin)
            ->get(route('admin.activity-date', ['date' => $date]))
            ->assertOk()
            ->assertViewIs('partials.admin-activity-card')
            ->assertViewHas('logs', fn($logs) => $logs->count() === 0)
            ->assertViewHas('totalCount', 0)
            ->assertSeeText('No activity recorded for this day');
    }

    public function test_admin_receives_error_for_invalid_date_format(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $invalidDate = 'not-a-date';

        $this->actingAs($admin)
            ->get(route('admin.activity-date', ['date' => $invalidDate]))
            ->assertStatus(400); // Expecting 400 Bad Request if validation is in place
    }
}
