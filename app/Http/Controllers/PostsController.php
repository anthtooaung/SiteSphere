<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostsRequest;
use App\Models\AuditLogs;
use App\Models\Categories;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\UserPosts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Categories::query()
            ->with(['tags' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('layout.upload-post', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $existingPost = Posts::query()
            ->where('url', $validated['url'])
            ->first();

        if ($existingPost && UserPosts::query()
            ->where('post_id', $existingPost->id)
            ->where('user_id', $user->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'url' => 'You have already reviewed this website.',
            ]);
        }

        // Check if URL is flagged as unsecure
        $unsecurePost = Posts::query()
            ->where('url', $validated['url'])
            ->where('is_unsecure', true)
            ->first();

        if ($unsecurePost) {
            return back()
                ->with('unsecure_post', [
                    'url' => $unsecurePost->url,
                    'slug' => $unsecurePost->slug,
                ])
                ->withInput();
        }

        DB::transaction(function () use ($existingPost, $user, $validated): void {
            $post = $existingPost ?? Posts::query()->create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title']),
                'url' => $validated['url'],
            ]);

            UserPosts::query()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'description' => $validated['description'],
                'user_hidden' => ! ($user->settings?->user_post_visible ?? true),
            ]);

            $post->tags()->syncWithoutDetaching($validated['tags']);
        });

        return redirect()
            ->route('home')
            ->with('success', 'Post created successfully.');
    }

    public function unban(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        $post = Posts::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($post, $user): void {
            UserPosts::query()
                ->where('post_id', $post->id)
                ->update(['user_hidden' => false]);

            $post->restore();

            AuditLogs::query()->create([
                'user_id' => $user->id,
                'action' => 'unban_post',
                'category' => 'resolved',
                'target_type' => Posts::class,
                'target_id' => $post->id,
                'reason' => 'Post unbanned and all descriptions restored by an admin.',
            ]);
        });

        return back()->with('success', 'Post unbanned and restored.');
    }

    public function toggleUnsecure(Request $request, Posts $post): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        $post->is_unsecure = ! $post->is_unsecure;
        $post->save();

        $action = $post->is_unsecure ? 'set_unsecure' : 'set_verified';
        $label = $post->is_unsecure ? 'unsecure' : 'secure';

        AuditLogs::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'category' => $post->is_unsecure ? 'moderation' : 'resolved',
            'target_type' => Posts::class,
            'target_id' => $post->id,
            'reason' => "Post marked as {$label} by an admin.",
        ]);

        return back()->with('success', "Post marked as {$label}.");
    }

    public function forceDelete(Request $request, Posts $post): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);
        abort_unless($post->trashed(), 404);

        DB::transaction(function () use ($post, $user): void {
            // Delete related records
            $post->comments()->forceDelete();
            $post->ratings()->delete();
            $post->bookmarks()->delete();
            UserPosts::where('post_id', $post->id)->forceDelete();

            AuditLogs::query()->create([
                'user_id' => $user->id,
                'action' => 'force_delete_post',
                'category' => 'moderation',
                'target_type' => Posts::class,
                'target_id' => $post->id,
                'reason' => 'Post permanently deleted by admin.',
            ]);

            $post->forceDelete();
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Post permanently deleted.');
    }


    public function deleteAudit(Request $request, UserPosts $userPost): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        $reason = $request->input('reason', 'No reason provided');

        $userPost->forceDelete();

        AuditLogs::query()->create([
            'user_id' => $user->id,
            'action' => 'delete_audit',
            'category' => 'moderation',
            'target_type' => UserPosts::class,
            'target_id' => $userPost->id,
            'reason' => 'Description permanently deleted by an admin. Reason: '.$reason,
        ]);

        return back()->with('success', 'Description permanently deleted.');
    }

    public function forceDeleteAudit(Request $request, UserPosts $userPost): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);
        abort_unless($userPost->trashed(), 404);

        $reason = $request->input('reason', 'No reason provided');

        AuditLogs::query()->create([
            'user_id' => $user->id,
            'action' => 'force_delete_audit',
            'category' => 'moderation',
            'target_type' => UserPosts::class,
            'target_id' => $userPost->id,
            'reason' => 'Description permanently deleted by admin. Reason: '.$reason,
        ]);

        $userPost->forceDelete();

        return back()->with('success', 'Description permanently deleted.');
    }

    public function restoreAudit(Request $request, UserPosts $userPost): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);
        abort_unless($userPost->trashed(), 404);

        AuditLogs::query()->create([
            'user_id' => $user->id,
            'action' => 'restore_audit',
            'category' => 'moderation',
            'target_type' => UserPosts::class,
            'target_id' => $userPost->id,
            'reason' => 'Description restored by admin.',
        ]);

        $userPost->restore();

        return back()->with('success', 'Description restored.');
    }

    private function uniqueSlug(string $title, ?Posts $ignorePost = null): string
    {
        $baseSlug = Str::slug($title) ?: 'post';
        $slug = $baseSlug;
        $counter = 2;

        while (Posts::query()
            ->where('slug', $slug)
            ->when($ignorePost, fn ($query) => $query->whereKeyNot($ignorePost->id))
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Display the specified resource.
     */
    public function show(Posts $posts): View
    {
        $isAdmin = auth()->user()?->role === 'admin';

        // Handle banned (soft-deleted) posts — legacy, admins only
        if ($posts->trashed()) {
            abort_unless($isAdmin, 404);

            $banLog = AuditLogs::query()
                ->with('user')
                ->where('target_type', Posts::class)
                ->where('target_id', $posts->id)
                ->where('action', 'ban_post')
                ->latest()
                ->first();

            $posts->load(['tags.categories', 'userPosts' => fn ($q) => $q->with('user.settings')->latest()]);
            $posts->loadCount(['ratings', 'comments', 'userPosts as audits_count']);
            $posts->loadAvg('ratings as average_rating', 'rating');

            return view('layout.post-detail', [
                'post' => $posts,
                'isBanned' => true,
                'banLog' => $banLog,
                'isUnsecure' => false,
                'averageRating' => round((float) ($posts->average_rating ?? 0), 1),
                'ratingsCount' => (int) $posts->ratings_count,
                'auditsCount' => (int) $posts->audits_count,
                'commentsCount' => (int) $posts->comments_count,
                'ratingDistribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
                'comments' => collect(),
                'commentUserRatings' => collect(),
                'relatedPosts' => collect(),
                'userRating' => 0,
                'userHasCommented' => false,
                'saved' => false,
            ]);
        }

        // Handle unsecure posts — visible to all, banner shown
        $isUnsecure = (bool) $posts->is_unsecure;

        $isAdmin = auth()->user()?->role === 'admin';

        $posts->load([
            'tags.categories',
            'userPosts' => fn ($query) => $query
                ->with('user.settings')
                ->when($isAdmin, fn ($q) => $q->withTrashed())
                ->latest(),
        ]);
        $posts->loadCount([
            'ratings',
            'comments',
            'userPosts as audits_count',
        ]);
        $posts->loadAvg('ratings as average_rating', 'rating');

        $userId = auth()->id();

        $averageRating = round((float) ($posts->average_rating ?? 0), 1);
        $ratingsCount = (int) $posts->ratings_count;
        $auditsCount = (int) $posts->audits_count;
        $commentsCount = (int) $posts->comments_count;

        // Calculate ratings distribution (1 to 5 stars)
        $ratingDistribution = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        ];
        if ($ratingsCount > 0) {
            $distributionData = $posts->ratings()
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating');
            foreach ($ratingDistribution as $stars => $count) {
                $ratingDistribution[$stars] = $distributionData->get($stars, 0);
            }
        }

        // Fetch user comments (User Reports) — admins can see banned comments
        $isAdmin = auth()->user()?->role === 'admin';
        $commentsQuery = $posts->comments()
            ->with([
                'user.settings',
                'commentReactions',
            ])
            ->withCount(['commentReactions as helpful_count' => fn ($query) => $query->where('helpful', true)]);

        if ($isAdmin) {
            $commentsQuery->withTrashed();
        }

        $comments = $commentsQuery->latest()->get();

        // Ratings keyed by user_id for this post (used in the comments section)
        $commentUserRatings = Ratings::query()
            ->where('post_id', $posts->id)
            ->whereIn('user_id', $comments->pluck('user_id')->unique())
            ->pluck('rating', 'user_id');

        // Related posts (horizontal scroll carousel)
        $tagIds = $posts->tags->pluck('id');
        $relatedPosts = Posts::query()
            ->whereKeyNot($posts->id)
            ->whereHas('tags', fn ($query) => $query->whereIn('tags.id', $tagIds))
            ->with([
                'userPosts',
            ])
            ->withAvg('ratings as average_rating', 'rating')
            ->withCount(['userPosts as audits_count'])
            ->latest()
            ->take(5)
            ->get();

        if ($relatedPosts->count() < 5) {
            $filler = Posts::query()
                ->whereKeyNot($posts->id)
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->with([
                    'userPosts',
                ])
                ->withAvg('ratings as average_rating', 'rating')
                ->withCount(['userPosts as audits_count'])
                ->latest()
                ->take(5 - $relatedPosts->count())
                ->get();
            $relatedPosts = $relatedPosts->concat($filler);
        }

        $userRating = $userId ? (Ratings::where('post_id', $posts->id)->where('user_id', $userId)->value('rating') ?? 0) : 0;
        $userHasCommented = $userId ? Comments::where('post_id', $posts->id)->where('user_id', $userId)->exists() : false;
        $saved = $userId ? $posts->bookmarks()->where('user_id', $userId)->exists() : false;

        return view('layout.post-detail', [
            'post' => $posts,
            'isBanned' => false,
            'isUnsecure' => $isUnsecure,
            'banLog' => null,
            'isUnsecure' => $posts->is_unsecure,
            'averageRating' => $averageRating,
            'ratingsCount' => $ratingsCount,
            'auditsCount' => $auditsCount,
            'commentsCount' => $commentsCount,
            'ratingDistribution' => $ratingDistribution,
            'comments' => $comments,
            'commentUserRatings' => $commentUserRatings,
            'relatedPosts' => $relatedPosts,
            'userRating' => $userRating,
            'userHasCommented' => $userHasCommented,
            'saved' => $saved,
        ]);
    }

    public function updateDescription(Request $request, Posts $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'description' => 'required|string|max:5000',
        ]);

        $post->userPosts()
            ->where('user_id', $request->user()->id)
            ->update(['description' => $validated['description']]);

        return back()->with('success', 'Description updated successfully.');
    }

    public function destroyDescription(Request $request, Posts $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->userPosts()
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('success', 'Description deleted successfully.');
    }
}
