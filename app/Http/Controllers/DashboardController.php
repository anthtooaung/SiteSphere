<?php

namespace App\Http\Controllers;

use App\Models\AuditLogs;
use App\Models\Bookmarks;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\Reports;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $totalUsers = User::count();
            $totalReviews = UserPosts::count();
            $totalReports = Reports::count();

            $recentAuditLogs = AuditLogs::query()
                ->latest()
                ->take(4)
                ->get();

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
                ],
                'recentActivity' => $recentAuditLogs,
                'topPosts' => $topPosts,
            ]);
        }

        $recentReviews = UserPosts::query()
            ->with(['post:id,title,slug,url'])
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
