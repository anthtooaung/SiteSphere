<?php

namespace App\Http\Controllers;

use App\Models\AuditLogs;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    /**
     * Display the activity log index with calendar data.
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $auditLogs = AuditLogs::query()
            ->with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        // Build slug mappings for audit log target linking
        $postIds = $auditLogs
            ->where('target_type', Posts::class)
            ->pluck('target_id')
            ->unique();

        $userIds = $auditLogs
            ->where('target_type', User::class)
            ->pluck('target_id')
            ->unique();

        $commentIds = $auditLogs
            ->where('target_type', Comments::class)
            ->pluck('target_id')
            ->unique();

        $postSlugs = Posts::withTrashed()
            ->whereIn('id', $postIds)
            ->pluck('slug', 'id');

        $userSlugs = User::withTrashed()
            ->whereIn('id', $userIds)
            ->pluck('slug', 'id');

        $commentPostSlugs = Comments::withTrashed()
            ->whereIn('id', $commentIds)
            ->with(['post' => fn ($q) => $q->select('id', 'slug')])
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => $c->post->slug ?? null])
            ->filter();

        $groupedLogs = $auditLogs->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));

        return view('layout.menu.activity-log', [
            'auditLogs' => $groupedLogs,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'postSlugs' => $postSlugs,
            'userSlugs' => $userSlugs,
            'commentPostSlugs' => $commentPostSlugs,
        ]);
    }

    /**
     * Display activity logs for a specific date.
     */
    public function show(Request $request, string $date): View
    {
        $this->authorizeAdmin($request);

        // Simple validation for YYYY-MM-DD
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(400, 'Invalid date format.');
        }

        $query = AuditLogs::query()
            ->with('user')
            ->whereDate('created_at', $date)
            ->latest();

        $totalCount = $query->count();
        $isFullList = $request->query('all') === 'true';

        $logs = $isFullList ? $query->get() : $query->take(3)->get();

        return view('partials.admin-activity-card', [
            'logs' => $logs,
            'date' => $date,
            'totalCount' => $totalCount,
            'isFullList' => $isFullList,
        ]);
    }

    /**
     * Authorize the current user as an admin.
     */
    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        abort_unless($user->role === 'admin', 403);

        return $user;
    }
}
