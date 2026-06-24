<?php

namespace App\Http\Controllers;

use App\Mail\UserAccountDeletedMail;
use App\Models\AuditLogs;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdminUsersController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'role' => in_array($request->query('role'), ['admin', 'user'], true) ? (string) $request->query('role') : 'all',
            'status' => in_array($request->query('status'), ['safe', 'warning', 'restricted'], true) ? (string) $request->query('status') : 'all',
            'joined_date' => trim((string) $request->query('joined_date', '')),
        ];

        $users = $this->filteredUsersQuery($filters)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('layout.menu.users', [
            'users' => $users,
            'userFilters' => $filters,
            'userSummary' => $this->summary(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);
        $this->abortIfSelfAction($admin, $user);

        $oldRole = $user->role;
        $newRole = $oldRole === 'admin' ? 'user' : 'admin';

        $user->forceFill(['role' => $newRole])->save();

        $this->audit($admin, 'change_user_role', $user, "Changed user role from {$oldRole} to {$newRole}.");

        return back()->with('success', "{$user->name}'s role was changed to {$newRole}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);
        $this->abortIfSelfAction($admin, $user);

        DB::transaction(function () use ($admin, $user): void {
            $user->status = 'banned';
            $user->banned_by = $admin->id;
            $user->banned_at = now();
            $user->ban_reason = $request->input('reason', 'No reason provided');
            $user->save();
            $user->delete(); // soft-delete (sets deleted_at)

            $this->audit($admin, 'ban_user', $user, 'User account was banned by an admin. Reason: '.$user->ban_reason);
        });

        Mail::to($user->email)->send(new UserAccountDeletedMail($user, $admin));

        return back()->with('success', "{$user->name}'s account was banned.");
    }

    public function restore(Request $request, User $user): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);

        $this->abortIfSelfAction($admin, $user);

        abort_unless($user->trashed(), 404);

        DB::transaction(function () use ($admin, $user): void {
            $user->status = 'verified';
            $user->banned_by = null;
            $user->banned_at = null;
            $user->ban_reason = null;
            $user->save();
            $user->restore(); // clears deleted_at

            $this->audit($admin, 'restore_user', $user, 'User account was restored by an admin.', category: 'resolved');
        });

        return back()->with('success', "{$user->name}'s account was restored.");
    }

    public function toggleUnsecure(Request $request, User $user): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);
        $this->abortIfSelfAction($admin, $user);

        $newStatus = $user->status === 'unsecure' ? 'verified' : 'unsecure';
        $user->status = $newStatus;
        $user->save();

        $action = $newStatus === 'unsecure' ? 'set_unsecure' : 'set_verified';
        $label = $newStatus === 'unsecure' ? 'unsecure' : 'secure';

        $this->audit($admin, $action, $user, "User marked as {$label} by an admin.", category: $newStatus === 'unsecure' ? 'moderation' : 'resolved');

        return back()->with('success', "{$user->name} marked as {$label}.");
    }

    public function forceDelete(Request $request, User $user): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);
        $this->abortIfSelfAction($admin, $user);

        abort_unless($user->trashed(), 404);

        DB::transaction(function () use ($admin, $user): void {
            // Delete user's uploaded content
            UserPosts::where('user_id', $user->id)->forceDelete();

            $this->audit($admin, 'force_delete_user', $user, 'User permanently deleted by admin.');

            $user->forceDelete();
        });

        return redirect()
            ->route('users')
            ->with('success', "{$user->name} permanently deleted.");
    }

    /**
     * @param  array{search: string, role: string, status: string, joined_date: string}  $filters
     */
    private function filteredUsersQuery(array $filters): Builder
    {
        return User::withTrashed()
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($filters): void {
                $query
                    ->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%")
                    ->orWhere('user_phone', 'like', "%{$filters['search']}%");
            }))
            ->when($filters['role'] !== 'all', fn (Builder $query) => $query->where('role', $filters['role']))
            ->when($filters['joined_date'] !== '', fn (Builder $query) => $query->whereDate('created_at', $filters['joined_date']))
            ->when($filters['status'] !== 'all', fn (Builder $query) => $this->applyStatusFilter($query, $filters['status']));
    }

    private function applyStatusFilter(Builder $query, string $status): void
    {
        match ($status) {
            'safe' => $query->whereNull('deleted_at')->where('report_count', 0),
            'warning' => $query->whereNull('deleted_at')->whereBetween('report_count', [1, 2]),
            'restricted' => $query->where(function (Builder $query): void {
                $query
                    ->whereNotNull('deleted_at')
                    ->orWhere('report_count', '>=', 3);
            }),
        };
    }

    private function summary(): array
    {
        $counts = User::withTrashed()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN deleted_at IS NULL AND report_count = 0 THEN 1 ELSE 0 END) as safe,
                SUM(CASE WHEN deleted_at IS NULL AND report_count BETWEEN 1 AND 2 THEN 1 ELSE 0 END) as warning,
                SUM(CASE WHEN deleted_at IS NOT NULL OR report_count >= 3 THEN 1 ELSE 0 END) as restricted
            ')
            ->first();

        return [
            'total' => (int) ($counts->total ?? 0),
            'safe' => (int) ($counts->safe ?? 0),
            'warning' => (int) ($counts->warning ?? 0),
            'restricted' => (int) ($counts->restricted ?? 0),
        ];
    }

    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        return $user;
    }

    private function abortIfSelfAction(User $admin, User $targetUser): void
    {
        abort_if($admin->is($targetUser), 403);
    }

    private function audit(User $admin, string $action, User $targetUser, string $reason, string $category = 'moderation'): void
    {
        AuditLogs::query()->create([
            'user_id' => $admin->id,
            'action' => $action,
            'category' => $category,
            'target_type' => User::class,
            'target_id' => $targetUser->id,
            'reason' => $reason,
        ]);
    }
}
