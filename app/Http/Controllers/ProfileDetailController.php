<?php

namespace App\Http\Controllers;

use App\Models\Ratings;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProfileDetailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ?string $slug = null): View|RedirectResponse
    {
        if ($slug) {
            $user = User::query()->where('slug', $slug)->firstOrFail();
        } else {
            $user = $request->user();

            // Redirect to slug-based URL to ensure consistent route style
            return redirect()->route('profile-detail', ['slug' => $user->slug]);
        }

        $isOwnProfile = $request->user()?->is($user) ?? false;

        $userPostsQuery = UserPosts::query()
            ->where('user_id', $user->id)
            ->when(! $isOwnProfile, fn ($query) => $query->where('user_hidden', false));

        $reviewsCount = (clone $userPostsQuery)->count();
        $uploadsCount = $reviewsCount; // Using reviews as uploads for now as per schema
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

        // All items for expanded lists
        $allReviews = (clone $userPostsQuery)
            ->with(['post.tags'])
            ->latest()
            ->get();

        $allRatings = Ratings::query()
            ->where('user_id', $user->id)
            ->with(['post'])
            ->latest()
            ->get();

        /** @var Collection<int, int> $recentReviewRatings */
        $recentReviewRatings = Ratings::query()
            ->where('user_id', $user->id)
            ->whereIn('post_id', $allReviews->pluck('post_id'))
            ->pluck('rating', 'post_id');

        return view('layout.profile-detail', [
            'user' => $user,
            'isOwnProfile' => $isOwnProfile,
            'reviewsCount' => $reviewsCount,
            'uploadsCount' => $uploadsCount,
            'ratingsCount' => $ratingsCount,
            'averageRating' => $averageRating,
            'recentReviews' => $recentReviews,
            'allReviews' => $allReviews,
            'allRatings' => $allRatings,
            'recentReviewRatings' => $recentReviewRatings,
        ]);
    }
}
