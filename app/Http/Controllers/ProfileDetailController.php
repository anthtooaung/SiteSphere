<?php

namespace App\Http\Controllers;

use App\Models\AuditLogs;
use App\Models\Comments;
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
            $user = User::withTrashed()->where('slug', $slug)->firstOrFail();
        } else {
            $user = $request->user();

            // Redirect to slug-based URL to ensure consistent route style
            return redirect()->route('profile-detail', ['slug' => $user->slug]);
        }

        // Handle banned (soft-deleted) users — admins only
        if ($user->trashed()) {
            abort_unless($request->user()?->role === 'admin', 404);

            $banLog = AuditLogs::query()
                ->with('user')
                ->where('target_type', User::class)
                ->where('target_id', $user->id)
                ->whereIn('action', ['delete_user', 'ban_user'])
                ->latest()
                ->first();

            return view('layout.profile-detail', [
                'user' => $user,
                'isBanned' => true,
                'banLog' => $banLog,
                'isOwnProfile' => false,
                'reviewsCount' => 0,
                'uploadsCount' => 0,
                'ratingsCount' => 0,
                'averageRating' => 0,
                'recentReviews' => collect(),
                'allReviews' => collect(),
                'allUploads' => collect(),
                'allRatings' => collect(),
                'recentReviewRatings' => collect(),
            ]);
        }

        $isOwnProfile = $request->user()?->is($user) ?? false;

        $userPostsQuery = UserPosts::query()
            ->has('post')
            ->where('user_id', $user->id)
            ->when(! $isOwnProfile, fn ($query) => $query->where('user_hidden', false));

        $uploadsCount = (clone $userPostsQuery)->count();

        $commentsQuery = Comments::query()
            ->has('post')
            ->where('user_id', $user->id);

        $reviewsCount = (clone $commentsQuery)->count();

        $ratingsCount = Ratings::query()
            ->where('user_id', $user->id)
            ->count();

        $postIds = (clone $userPostsQuery)->pluck('post_id');
        $averageRating = Ratings::query()
            ->whereIn('post_id', $postIds)
            ->avg('rating') ?: 0;

        $recentReviews = (clone $commentsQuery)
            ->with(['post.tags'])
            ->latest()
            ->take(4)
            ->get();

        // All items for expanded lists
        $allReviews = (clone $commentsQuery)
            ->with(['post.tags'])
            ->latest()
            ->get();

        $allUploads = (clone $userPostsQuery)
            ->with(['post.tags', 'post.ratings'])
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
            'isBanned' => false,
            'banLog' => null,
            'isOwnProfile' => $isOwnProfile,
            'reviewsCount' => $reviewsCount,
            'uploadsCount' => $uploadsCount,
            'ratingsCount' => $ratingsCount,
            'averageRating' => $averageRating,
            'recentReviews' => $recentReviews,
            'allReviews' => $allReviews,
            'allUploads' => $allUploads,
            'allRatings' => $allRatings,
            'recentReviewRatings' => $recentReviewRatings,
            'maskedEmail' => maskEmail($user->email),
        ]);
    }
}
