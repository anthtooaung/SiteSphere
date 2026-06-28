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
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
