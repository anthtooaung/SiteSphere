<?php

namespace App\Http\Controllers;

use App\Models\AuditLogs;
use App\Models\Bookmarks;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\Reports;
use App\Models\User;
use App\Models\UserPosts;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Return dashboard stats filtered by year/month as JSON.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $totalUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalReviews = UserPosts::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalReports = Reports::whereBetween('created_at', [$startDate, $endDate])->count();

        // Trend: daily counts for the 10 days before the selected month end
        $getTrend = function ($model) use ($endDate) {
            $days = 10;
            $trendStart = $endDate->copy()->subDays($days - 1)->startOfDay();
            $data = $model::query()
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->whereBetween('created_at', [$trendStart, $endDate->endOfDay()])
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $trend = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = $endDate->copy()->subDays($i)->format('Y-m-d');
                $trend[] = $data[$date] ?? 0;
            }

            return $trend;
        };

        $userTrend = $getTrend(User::class);
        $reviewTrend = $getTrend(UserPosts::class);
        $reportTrend = $getTrend(Reports::class);

        $recentAuditLogs = AuditLogs::query()
            ->with('user')
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->latest()
            ->take(7)
            ->get();

        // Build slug mappings
        $postIds = $recentAuditLogs
            ->where('target_type', Posts::class)
            ->pluck('target_id')
            ->unique();

        $userIds = $recentAuditLogs
            ->where('target_type', User::class)
            ->pluck('target_id')
            ->unique();

        $commentIds = $recentAuditLogs
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

        $topPosts = Posts::query()
            ->with(['tags.categories'])
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->withAvg('ratings', 'rating')
            ->withCount('comments')
            ->orderBy('ratings_avg_rating', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                [
                    'name' => 'Site Reviews',
                    'value' => $totalReviews,
                    'logVal' => log10(max(1, $totalReviews)),
                    'color' => '#8b5cf6',
                    'icon' => 'magnifying-glass',
                    'trendHtml' => '',
                    'trend' => $reviewTrend,
                ],
                [
                    'name' => 'Total Users',
                    'value' => $totalUsers,
                    'logVal' => log10(max(1, $totalUsers)),
                    'color' => '#6366f1',
                    'icon' => 'users',
                    'trendHtml' => '',
                    'trend' => $userTrend,
                ],
                [
                    'name' => 'Open Reports',
                    'value' => $totalReports,
                    'logVal' => log10(max(1, $totalReports)),
                    'color' => '#ef4444',
                    'icon' => 'flag',
                    'trendHtml' => '',
                    'trend' => $reportTrend,
                ],
            ],
            'recentActivity' => $recentAuditLogs->map(fn ($log) => [
                'color' => $log->getColor(),
                'category' => $log->category,
                'user' => $log->user?->name ?? 'System',
                'txt' => $log->action,
                'target' => $log->target_type ? class_basename($log->target_type) : null,
                'targetId' => $log->target_id,
                'targetType' => $log->target_type,
                'reason' => $log->reason,
                'time' => $log->created_at->diffForHumans(),
            ])->toArray(),
            'topPosts' => $topPosts->map(fn ($post) => [
                'title' => $post->domain,
                'slug' => $post->slug,
                'rating' => round($post->ratings_avg_rating ?? 0),
                'comments' => $post->comments_count,
            ])->toArray(),
            'postSlugs' => $postSlugs,
            'userSlugs' => $userSlugs,
            'commentPostSlugs' => $commentPostSlugs,
        ]);
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user && $user->isAdmin()) {
            $totalUsers = User::count();
            $totalReviews = UserPosts::count();
            $totalReports = Reports::count();

            $getTrend = function ($model) {
                $days = 10;
                $data = $model::query()
                    ->selectRaw('DATE(created_at) as date, count(*) as count')
                    ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date')
                    ->toArray();

                $trend = [];
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $trend[] = $data[$date] ?? 0;
                }

                return $trend;
            };

            $userTrend = $getTrend(User::class);
            $reviewTrend = $getTrend(UserPosts::class);
            $reportTrend = $getTrend(Reports::class);

            $recentAuditLogs = AuditLogs::query()
                ->with('user')
                ->latest()
                ->take(7)
                ->get();

            // Build slug mappings for audit log target linking
            $postIds = $recentAuditLogs
                ->where('target_type', Posts::class)
                ->pluck('target_id')
                ->unique();

            $userIds = $recentAuditLogs
                ->where('target_type', User::class)
                ->pluck('target_id')
                ->unique();

            $commentIds = $recentAuditLogs
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

            $topPosts = Posts::query()
                ->with(['tags.categories'])
                ->withAvg('ratings', 'rating')
                ->withCount('comments')
                ->orderBy('ratings_avg_rating', 'desc')
                ->take(5)
                ->get();

            return view('layout.menu.dashboard', [
                'isAdmin' => true,
                'stats' => [
                    'totalUsers' => $totalUsers,
                    'totalReviews' => $totalReviews,
                    'totalReports' => $totalReports,
                    'userTrend' => $userTrend,
                    'reviewTrend' => $reviewTrend,
                    'reportTrend' => $reportTrend,
                ],
                'recentActivity' => $recentAuditLogs,
                'topPosts' => $topPosts,
                'postSlugs' => $postSlugs,
                'userSlugs' => $userSlugs,
                'commentPostSlugs' => $commentPostSlugs,
            ]);
        }

        if (! $user) {
            return redirect()->route('login');
        }

        $recentReviews = UserPosts::query()
            ->with(['post:id,slug,url'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->take(4)
            ->get();

        return view('layout.menu.dashboard', [
            'isAdmin' => false,
            'stats' => [
                'totalReviews' => UserPosts::query()
                    ->where('user_id', $user->id)
                    ->count(),
                'savedPosts' => Bookmarks::query()
                    ->where('user_id', $user->id)
                    ->count(),
                'ratingsGiven' => Ratings::query()
                    ->where('user_id', $user->id)
                    ->count(),
                'reviewedWebsites' => Posts::query()
                    ->whereHas('userPosts', fn ($query) => $query->where('user_id', $user->id))
                    ->count(),
            ],
            'recentReviews' => $recentReviews,
        ]);
    }
}
