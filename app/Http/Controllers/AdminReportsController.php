<?php

namespace App\Http\Controllers;

use App\Models\Reports;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminReportsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => in_array($request->query('status'), ['all', 'unread', 'read'], true) ? (string) $request->query('status') : 'unread',
            'reported_date' => trim((string) $request->query('reported_date', '')),
        ];

        $reports = Reports::query()
            ->where('target_name', 'posts')
            ->with([
                'post:id,title,url',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applySearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('admin_read', false))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('admin_read', true))
            ->when($filters['reported_date'] !== '', fn (Builder $query) => $query->whereDate('created_at', $filters['reported_date']))
            ->latest()
            ->paginate(12, ['*'], 'posts_page')
            ->withQueryString();

        $commentReports = Reports::query()
            ->where('target_name', 'comments')
            ->with([
                'comment:id,content',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applyCommentSearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('admin_read', false))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('admin_read', true))
            ->when($filters['reported_date'] !== '', fn (Builder $query) => $query->whereDate('created_at', $filters['reported_date']))
            ->latest()
            ->paginate(12, ['*'], 'comments_page')
            ->withQueryString();

        $userReports = Reports::query()
            ->where('target_name', 'users')
            ->with([
                'targetUser:id,name,email,user_image',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applyUserSearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('admin_read', false))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('admin_read', true))
            ->when($filters['reported_date'] !== '', fn (Builder $query) => $query->whereDate('created_at', $filters['reported_date']))
            ->latest()
            ->paginate(12, ['*'], 'users_page')
            ->withQueryString();

        $activeTab = 'posts';
        if ($request->has('comments_page')) {
            $activeTab = 'comments';
        } elseif ($request->has('users_page')) {
            $activeTab = 'users';
        }

        return view('layout.menu.reports', [
            'highlightPostId' => (int) $request->query('report', 0),
            'reportFilters' => $filters,
            'reportSummary' => $this->summary(),
            'reports' => $reports,
            'commentReports' => $commentReports,
            'userReports' => $userReports,
            'activeTab' => $activeTab,
        ]);
    }

    public function markRead(Request $request, Reports $report): RedirectResponse
    {
        $this->authorizeAdmin($request);

        abort_unless(in_array($report->target_name, ['posts', 'comments', 'users'], true), 404);

        $report->forceFill(['admin_read' => true])->save();

        return back()->with('success', 'Report marked as read.');
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('reason', 'like', "%{$search}%")
                ->orWhere('target_id', 'like', "%{$search}%")
                ->orWhereHas('post', function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%");
                })
                ->orWhereHas('reporter', function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    private function applyCommentSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('reason', 'like', "%{$search}%")
                ->orWhere('target_id', 'like', "%{$search}%")
                ->orWhereHas('comment', function (Builder $query) use ($search): void {
                    $query->where('content', 'like', "%{$search}%");
                })
                ->orWhereHas('reporter', function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    private function applyUserSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('reason', 'like', "%{$search}%")
                ->orWhere('target_id', 'like', "%{$search}%")
                ->orWhereHas('targetUser', function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('reporter', function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $baseQuery = Reports::query();

        return [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->where('admin_read', false)->count(),
            'read' => (clone $baseQuery)->where('admin_read', true)->count(),
        ];
    }

    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        return $user;
    }
}
