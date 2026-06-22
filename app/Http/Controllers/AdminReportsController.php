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
                'post:id,title,slug,url,deleted_at',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applySearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('status', Reports::STATUS_NEW))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('status', '!=', Reports::STATUS_NEW))
            ->when($filters['status'] === 'all', fn (Builder $query) => $query->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END"))
            ->when($filters['reported_date'] !== '', fn (Builder $query) => $query->whereDate('created_at', $filters['reported_date']))
            ->latest()
            ->paginate(12, ['*'], 'posts_page')
            ->withQueryString();

        $commentReports = Reports::query()
            ->where('target_name', 'comments')
            ->with([
                'comment:id,content,post_id,deleted_at',
                'comment.post:id,slug',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applyCommentSearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('status', Reports::STATUS_NEW))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('status', '!=', Reports::STATUS_NEW))
            ->when($filters['status'] === 'all', fn (Builder $query) => $query->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END"))
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
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('status', Reports::STATUS_NEW))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('status', '!=', Reports::STATUS_NEW))
            ->when($filters['status'] === 'all', fn (Builder $query) => $query->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END"))
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
        $admin = $this->authorizeAdmin($request);

        abort_unless(in_array($report->target_name, ['posts', 'comments', 'users'], true), 404);

        if ($report->isNew()) {
            $report->transitionTo(Reports::STATUS_PENDING, 'Report opened by admin.');

            AuditLogs::query()->create([
                'user_id' => $admin->id,
                'action' => 'read_report',
                'target_type' => Reports::class,
                'target_id' => $report->id,
                'reason' => 'Report marked as read.',
            ]);
        }

        return back()->with('success', 'Report marked as read.');
    }

    public function open(Request $request, Reports $report): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);

        abort_unless(in_array($report->target_name, ['posts', 'comments', 'users'], true), 404);

        if ($report->isNew()) {
            $report->transitionTo(Reports::STATUS_PENDING, 'Report opened by admin.');

            AuditLogs::query()->create([
                'user_id' => $admin->id,
                'action' => 'read_report',
                'target_type' => Reports::class,
                'target_id' => $report->id,
                'reason' => 'Report opened and marked as read.',
            ]);
        }

        if ($report->target_name === 'posts') {
            $post = Posts::find($report->target_id);
            if ($post) {
                return redirect()->route('posts.show', $post->slug);
            }
        } elseif ($report->target_name === 'comments') {
            $comment = Comments::withTrashed()->with('post')->find($report->target_id);
            if ($comment && $comment->post) {
                return redirect()->route('posts.show', $comment->post->slug)
                    ->withFragment("comment-{$comment->id}");
            }
        } elseif ($report->target_name === 'users') {
            $targetUser = User::find($report->target_id);
            if ($targetUser) {
                return redirect()->route('profile-detail', $targetUser->name);
            }
        }

        return back()->with('error', 'Target content not found.');
    }

    public function updateStatus(Request $request, Reports $report): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);

        $validated = $request->validate([
            'status' => 'required|string|in:'.implode(',', [
                Reports::STATUS_PENDING,
                Reports::STATUS_INVESTIGATING,
                Reports::STATUS_RESOLVED_ACTION,
                Reports::STATUS_RESOLVED_NO_ACTION,
                Reports::STATUS_DISMISSED,
                Reports::STATUS_CLOSED,
            ]),
            'reason' => 'nullable|string|max:500',
        ]);

        if (! $report->transitionTo($validated['status'], $validated['reason'] ?? null)) {
            return back()->with('error', 'Invalid status transition.');
        }

        AuditLogs::query()->create([
            'user_id' => $admin->id,
            'action' => 'update_report_status',
            'target_type' => Reports::class,
            'target_id' => $report->id,
            'reason' => "Report status changed to {$validated['status']}.",
        ]);

        return back()->with('success', 'Report status updated.');
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
            ->selectRaw('target_name, status, count(*) as count')
            ->groupBy('target_name', 'status')
            ->get();

        $summary = [
            'posts' => ['total' => 0, 'unread' => 0, 'read' => 0],
            'comments' => ['total' => 0, 'unread' => 0, 'read' => 0],
            'users' => ['total' => 0, 'unread' => 0, 'read' => 0],
        ];

        foreach ($counts as $row) {
            $target = $row->target_name;
            if (isset($summary[$target])) {
                if ($row->status === Reports::STATUS_NEW) {
                    $summary[$target]['unread'] += $row->count;
                } else {
                    $summary[$target]['read'] += $row->count;
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
        $admin = $this->authorizeAdmin($request);

        abort_unless(in_array($report->target_name, ['posts', 'comments', 'users'], true), 404);

        if (! $report->isNew()) {
            $report->transitionTo(Reports::STATUS_NEW, 'Report marked as unread by admin.');

            AuditLogs::query()->create([
                'user_id' => $admin->id,
                'action' => 'unread_report',
                'target_type' => Reports::class,
                'target_id' => $report->id,
                'reason' => 'Report marked as unread.',
            ]);
        }

        return back()->with('success', 'Report marked as unread.');
    }

    public function destroy(Request $request, Reports $report): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);

        $reportId = $report->id;
        $report->delete();

        AuditLogs::query()->create([
            'user_id' => $admin->id,
            'action' => 'delete_report',
            'target_type' => Reports::class,
            'target_id' => $reportId,
            'reason' => 'Report record deleted by an admin.',
        ]);

        return back()->with('success', 'Report has been deleted.');
    }
}
