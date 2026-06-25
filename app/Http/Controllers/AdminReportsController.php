<?php

namespace App\Http\Controllers;

use App\Models\AuditLogs;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\Reports;
use App\Models\User;
use App\Models\UserPosts;
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
                'comment:id,content,post_id,deleted_at',
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

        $userPostReports = Reports::query()
            ->where('target_name', 'user_posts')
            ->with([
                'userPost:id,post_id,user_id,description,deleted_at',
                'userPost.user:id,name',
                'reporter:id,name,email,user_image',
            ])
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applyUserPostSearch($query, $filters['search']))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('admin_read', false))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('admin_read', true))
            ->when($filters['status'] === 'all', fn (Builder $query) => $query->orderBy('admin_read', 'asc'))
            ->when($filters['reported_date'] !== '', fn (Builder $query) => $query->whereDate('created_at', $filters['reported_date']))
            ->latest()
            ->paginate(12, ['*'], 'user_posts_page')
            ->withQueryString();

        $activeTab = 'users';
        if ($request->has('posts_page')) {
            $activeTab = 'posts';
        } elseif ($request->has('comments_page')) {
            $activeTab = 'comments';
        } elseif ($request->has('user_posts_page')) {
            $activeTab = 'user_posts';
        }

        return view('layout.menu.reports', [
            'highlightPostId' => (int) $request->query('report', 0),
            'reportFilters' => $filters,
            'reportSummary' => $this->summary(),
            'reports' => $reports,
            'commentReports' => $commentReports,
            'userReports' => $userReports,
            'userPostReports' => $userPostReports,
            'activeTab' => $activeTab,
        ]);
    }

    public function markRead(Request $request, Reports $report): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);

        abort_unless(in_array($report->target_name, ['posts', 'comments', 'users', 'user_posts'], true), 404);

        if (! $report->admin_read) {
            $report->forceFill(['admin_read' => true])->save();

            AuditLogs::query()->create([
                'user_id' => $admin->id,
                'action' => 'read_report',
                'category' => 'check',
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

        abort_unless(in_array($report->target_name, ['posts', 'comments', 'users', 'user_posts'], true), 404);

        if (! $report->admin_read) {
            $report->forceFill(['admin_read' => true])->save();

            AuditLogs::query()->create([
                'user_id' => $admin->id,
                'action' => 'read_report',
                'category' => 'check',
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
                return redirect()->route('profile-detail', ['slug' => $targetUser->slug]);
            }
        } elseif ($report->target_name === 'user_posts') {
            $userPost = UserPosts::find($report->target_id);
            if ($userPost && $userPost->post) {
                return redirect()->route('posts.show', $userPost->post->slug)
                    ->withFragment("panel-user-{$userPost->user_id}");
            }
        }

        return back()->with('error', 'Target content not found.');
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

    private function applyUserPostSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('reason', 'like', "%{$search}%")
                ->orWhere('target_id', 'like', "%{$search}%")
                ->orWhereHas('userPost', function (Builder $query) use ($search): void {
                    $query
                        ->where('description', 'like', "%{$search}%");
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
            'user_posts' => ['total' => 0, 'unread' => 0, 'read' => 0],
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
        $admin = $this->authorizeAdmin($request);

        abort_unless(in_array($report->target_name, ['posts', 'comments', 'users', 'user_posts'], true), 404);

        if ($report->admin_read) {
            $report->forceFill(['admin_read' => false])->save();

            AuditLogs::query()->create([
                'user_id' => $admin->id,
                'action' => 'unread_report',
                'category' => 'check',
                'target_type' => Reports::class,
                'target_id' => $report->id,
                'reason' => 'Report marked as unread.',
            ]);
        }

        return back()->with('success', 'Report marked as unread.');
    }

    public function resolve(Request $request, Reports $report): RedirectResponse
    {
        $admin = $this->authorizeAdmin($request);

        $targetName = $report->target_name;
        $targetId = $report->target_id;

        // Mark all reports for this target as read (keep them in table)
        Reports::query()
            ->where('target_name', $targetName)
            ->where('target_id', $targetId)
            ->update(['admin_read' => true]);

        // Reset report_count on the target
        $this->resetReportCount($targetName, $targetId);

        AuditLogs::query()->create([
            'user_id' => $admin->id,
            'action' => 'resolve_report',
            'category' => 'resolved',
            'target_type' => Reports::class,
            'target_id' => $report->id,
            'reason' => "Resolved reports for {$targetName} #{$targetId}.",
        ]);

        return back()->with('success', 'Reports resolved.');
    }

    private function resetReportCount(string $targetName, int $targetId): void
    {
        match ($targetName) {
            'posts' => Posts::where('id', $targetId)->update(['report_count' => 0]),
            'comments' => Comments::where('id', $targetId)->update(['report_count' => 0]),
            'users' => User::where('id', $targetId)->update(['report_count' => 0]),
            'user_posts' => UserPosts::where('id', $targetId)->update(['report_count' => 0]),
            default => null,
        };
    }
}
