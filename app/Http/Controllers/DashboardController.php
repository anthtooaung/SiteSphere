<?php

namespace App\Http\Controllers;

use App\Models\Bookmarks;
use App\Models\Posts;
use App\Models\Ratings;
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

        $recentReviews = UserPosts::query()
            ->with(['post:id,title,slug,url'])
            ->where('user_id', $user->id)
            ->where('user_hidden', false)
            ->latest('id')
            ->take(4)
            ->get();

        return view('layout.menu.dashboard', [
            'stats' => [
                'visibleReviews' => UserPosts::query()
                    ->where('user_id', $user->id)
                    ->where('user_hidden', false)
                    ->count(),
                'savedPosts' => Bookmarks::query()
                    ->where('user_id', $user->id)
                    ->count(),
                'ratingsGiven' => Ratings::query()
                    ->where('user_id', $user->id)
                    ->count(),
                'reviewedWebsites' => Posts::query()
                    ->whereHas('userPosts', fn ($query) => $query->where('user_hidden', false))
                    ->count(),
            ],
            'recentReviews' => $recentReviews,
        ]);
    }
}
