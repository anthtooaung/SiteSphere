<?php

namespace App\Http\Controllers;

use App\Models\Ratings;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProfileDetailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ?string $slug = null): View
    {
        $user = $slug
            ? User::query()->where('slug', $slug)->firstOrFail()
            : $request->user();

        $isOwnProfile = $request->user()?->is($user) ?? false;

        $userPostsQuery = UserPosts::query()
            ->where('user_id', $user->id)
            ->when(! $isOwnProfile, fn ($query) => $query->where('user_hidden', false));

        $reviewsCount = (clone $userPostsQuery)->count();
        $ratingsCount = Ratings::query()
            ->where('user_id', $user->id)
            ->count();
        $postIds = (clone $userPostsQuery)->pluck('post_id');
        $averageRating = Ratings::query()
            ->whereIn('post_id', $postIds)
            ->avg('rating') ?: 0;
        $recentReviews = (clone $userPostsQuery)
            ->with(['post.tags'])
            ->latest()
            ->take(4)
            ->get();

        /** @var Collection<int, int> $recentReviewRatings */
        $recentReviewRatings = Ratings::query()
            ->where('user_id', $user->id)
            ->whereIn('post_id', $recentReviews->pluck('post_id'))
            ->pluck('rating', 'post_id');

        return view('layout.profile-detail', [
            'user' => $user,
            'isOwnProfile' => $isOwnProfile,
            'reviewsCount' => $reviewsCount,
            'ratingsCount' => $ratingsCount,
            'averageRating' => $averageRating,
            'recentReviews' => $recentReviews,
            'recentReviewRatings' => $recentReviewRatings,
        ]);
    }
}
