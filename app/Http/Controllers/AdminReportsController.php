<?php

namespace App\Http\Controllers;

use App\Models\AuditLogs;
use App\Models\Comments;
use App\Models\Posts;
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
                'post:id,title,slug,url',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applySearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('admin_read', false))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('admin_read', true))
            ->when($filters['status'] === 'all', fn (Builder $query) => $query->orderBy('admin_read', 'asc'))
            ->when($filters['reported_date'] !== '', fn (Builder $query) => $query->whereDate('created_at', $filters['reported_date']))
            ->latest()
            ->paginate(12, ['*'], 'posts_page')
            ->withQueryString();

        $commentReports = Reports::query()
            ->where('target_name', 'comments')
            ->with([
                'comment:id,content,post_id',
                'comment.post:id,slug',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applyCommentSearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('admin_read', false))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('admin_read', true))
            ->when($filters['status'] === 'all', fn (Builder $query) => $query->orderBy('admin_read', 'asc'))
            ->when($filters['reported_date'] !== '', fn (Builder $query) => $query->whereDate('created_at', $filters['reported_date']))
            ->latest()
            ->paginate(12, ['*'], 'comments_page')
            ->withQueryString();

        $userReports = Reports::query()
            ->where('target_name', 'users')
            ->with([
                'targetUser:id,name,slug,email,user_image',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applyUserSearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('admin_read', false))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('admin_read', true))
            ->when($filters['status'] === 'all', fn (Builder $query) => $query->orderBy('admin_read', 'asc'))
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

    private function summary(): array
    {
        $counts = Reports::query()
            ->selectRaw('target_name, admin_read, count(*) as count')
            ->groupBy('target_name', 'admin_read')
            ->get();

        $summary = [
            'posts' => ['total' => 0, 'unread' => 0, 'read' => 0],
            'comments' => ['total' => 0, 'unread' => 0, 'read' => 0],
            'users' => ['total' => 0, 'unread' => 0, 'read' => 0],
        ];

        foreach ($counts as $row) {
            $target = $row->target_name;
            if (isset($summary[$target])) {
                if ($row->admin_read) {
                    $summary[$target]['read'] += $row->count;
                } else {
                    $summary[$target]['unread'] += $row->count;
                }
                $summary[$target]['total'] += $row->count;
            }
        }

        return $summary;
    }

    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        return $user;
    }

    public function markUnread(Request $request, Reports $report): RedirectResponse
    {
        $this->authorizeAdmin($request);

        abort_unless(in_array($report->target_name, ['posts', 'comments', 'users'], true), 404);

        $report->forceFill(['admin_read' => false])->save();

        return back()->with('success', 'Report marked as unread.');
    }

    public function deletePost(Request $request, int $id): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);

        $post = Posts::findOrFail($id);
        $post->delete();

        AuditLogs::query()->create([
            'user_id' => $admin->id,
            'action' => 'delete_post',
            'target_type' => Posts::class,
            'target_id' => $post->id,
            'reason' => 'Post soft deleted by an admin from the reports dashboard.',
        ]);

        return back()->with('success', 'Post has been soft deleted.');
    }

    public function deleteComment(Request $request, int $id): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);

        $comment = Comments::findOrFail($id);
        $comment->delete();

        AuditLogs::query()->create([
            'user_id' => $admin->id,
            'action' => 'delete_comment',
            'target_type' => Comments::class,
            'target_id' => $comment->id,
            'reason' => 'Comment soft deleted by an admin from the reports dashboard.',
        ]);

        return back()->with('success', 'Comment has been soft deleted.');
    }
}
