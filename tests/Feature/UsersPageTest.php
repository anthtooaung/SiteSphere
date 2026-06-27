<?php

namespace Tests\Feature;

use App\Mail\UserAccountDeletedMail;
use App\Models\AuditLogs;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UsersPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('users'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_receive_forbidden_response(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('users'))
            ->assertForbidden();
    }

    public function test_admin_sees_users_table_summary_filters_and_active_menu_link(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Owner', 'role' => 'admin']);
        $safeUser = User::factory()->create(['name' => 'Safe Reader', 'role' => 'user', 'report_count' => 0]);
        $warningUser = User::factory()->create(['name' => 'Warning Writer', 'role' => 'user', 'report_count' => 2]);
        $restrictedUser = User::factory()->create(['name' => 'Restricted Member', 'role' => 'user']);
        $restrictedUser->delete();

        $this->actingAs($admin)
            ->get(route('users'))
            ->assertOk()
            ->assertSee('data-users-page', false)
            ->assertSee('data-users-filter-form', false)
            ->assertSee('data-users-search', false)
            ->assertSee('data-users-role-filter', false)
            ->assertSee('data-users-status-filter', false)
            ->assertSee('data-users-joined-date', false)
            ->assertSee('Total Users')
            ->assertSee('Safe')
            ->assertSee('Warning')
            ->assertSee('Restricted')
            ->assertSee('Safe Reader')
            ->assertSee('Warning Writer')
            ->assertSee('Restricted Member')
            ->assertSee('Restricted account')
            ->assertSee('href="'.route('users').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_search_role_status_and_joined_date_filters_work(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matchingUser = User::factory()->create([
            'name' => 'Filtered Person',
            'email' => 'filtered@example.com',
            'role' => 'user',
            'report_count' => 0,
            'created_at' => Carbon::parse('2026-05-10 09:00:00'),
        ]);
        $otherUser = User::factory()->create([
            'name' => 'Other Person',
            'email' => 'other@example.com',
            'role' => 'admin',
            'report_count' => 0,
            'created_at' => Carbon::parse('2026-05-11 09:00:00'),
        ]);
        $restrictedUser = User::factory()->create([
            'name' => 'Restricted Filter Target',
            'role' => 'user',
            'report_count' => 4,
            'created_at' => Carbon::parse('2026-05-10 11:00:00'),
        ]);

        $this->actingAs($admin)
            ->get(route('users', ['search' => 'filtered@example.com']))
            ->assertOk()
            ->assertSee($matchingUser->name)
            ->assertDontSee($otherUser->name);

        $this->actingAs($admin)
            ->get(route('users', ['role' => 'admin']))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertSee($otherUser->name)
            ->assertDontSee($matchingUser->name);

        $this->actingAs($admin)
            ->get(route('users', ['status' => 'restricted']))
            ->assertOk()
            ->assertSee($restrictedUser->name)
            ->assertDontSee($matchingUser->name);

        $this->actingAs($admin)
            ->get(route('users', ['joined_date' => '2026-05-10']))
            ->assertOk()
            ->assertSee($matchingUser->name)
            ->assertSee($restrictedUser->name)
            ->assertDontSee($otherUser->name);
    }

    public function test_admin_can_change_another_users_role_and_audit_log_is_created(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->from(route('users'))
            ->patch(route('users.role', $targetUser))
            ->assertRedirect(route('users'))
            ->assertSessionHas('success', "{$targetUser->name}'s role was changed to admin.");

        $this->assertSame('admin', $targetUser->fresh()->role);
        $this->assertDatabaseHas((new AuditLogs)->getTable(), [
            'user_id' => $admin->id,
            'action' => 'change_user_role',
            'target_type' => User::class,
            'target_id' => $targetUser->id,
        ]);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('users.role', $admin))
            ->assertForbidden();

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_can_soft_delete_user_send_mail_and_create_audit_log(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->from(route('users'))
            ->delete(route('users.destroy', $targetUser))
            ->assertRedirect(route('users'))
            ->assertSessionHas('success', "{$targetUser->name}'s account was banned.");

        $this->assertTrue(User::withTrashed()->find($targetUser->id)->trashed());
        Mail::assertSent(UserAccountDeletedMail::class, fn (UserAccountDeletedMail $mail) => $mail->deletedUser->is($targetUser)
            && $mail->admin->is($admin));
        $this->assertDatabaseHas((new AuditLogs)->getTable(), [
            'user_id' => $admin->id,
            'action' => 'delete_user',
            'target_type' => User::class,
            'target_id' => $targetUser->id,
        ]);
    }

    public function test_soft_deleted_users_cannot_log_in(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->delete(route('users.destroy', $targetUser));
        $this->post(route('logout'));

        $this->post('/login', [
            'email' => $targetUser->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertForbidden();

        $this->assertFalse($admin->fresh()->trashed());
    }

    public function test_admin_can_restore_soft_deleted_user_and_audit_log_is_created(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);
        $targetUser->delete();

        $this->actingAs($admin)
            ->from(route('users'))
            ->patch(route('users.restore', $targetUser->id))
            ->assertRedirect(route('users'))
            ->assertSessionHas('success', "{$targetUser->name}'s account was restored.");

        $this->assertFalse(User::withTrashed()->find($targetUser->id)->trashed());
        $this->assertDatabaseHas((new AuditLogs)->getTable(), [
            'user_id' => $admin->id,
            'action' => 'restore_user',
            'target_type' => User::class,
            'target_id' => $targetUser->id,
        ]);
    }

    public function test_soft_deleted_users_appear_as_restricted_and_restorable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['name' => 'Rollback Candidate', 'role' => 'user']);
        $targetUser->delete();

        $this->actingAs($admin)
            ->get(route('users'))
            ->assertOk()
            ->assertSee('Rollback Candidate')
            ->assertSee('Restricted account')
            ->assertSee('action="'.route('users.restore', $targetUser->id).'"', false)
            ->assertSee('Restore Rollback Candidate', false);
    }
}
