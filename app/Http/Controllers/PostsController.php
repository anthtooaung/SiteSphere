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

    public function ban(Request $request, Posts $post): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        DB::transaction(function () use ($post, $user): void {
            UserPosts::query()
                ->where('post_id', $post->id)
                ->update(['user_hidden' => true]);

            $post->delete();

            AuditLogs::query()->create([
                'user_id' => $user->id,
                'action' => 'ban_post',
                'target_type' => Posts::class,
                'target_id' => $post->id,
                'reason' => 'Post soft deleted and all descriptions hidden by an admin.',
            ]);
        });

        return redirect()
            ->route('home')
            ->with('success', 'Post banned and soft deleted.');
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
                'target_type' => Posts::class,
                'target_id' => $post->id,
                'reason' => 'Post unbanned and all descriptions restored by an admin.',
            ]);
        });

        return back()->with('success', 'Post unbanned and restored.');
    }

    public function banAudit(Request $request, UserPosts $userPost): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        $userPost->update(['user_hidden' => true]);

        AuditLogs::query()->create([
            'user_id' => $user->id,
            'action' => 'ban_audit',
            'target_type' => UserPosts::class,
            'target_id' => $userPost->id,
            'reason' => 'Audit description hidden by an admin.',
        ]);

        return back()->with('success', 'Audit description hidden.');
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
        $posts->load([
            'tags.categories',
            'userPosts' => fn ($query) => $query
                ->with('user.settings')
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

        // Fetch user comments (User Reports)
        $comments = $posts->comments()
            ->with([
                'user.settings',
                'commentReactions',
            ])
            ->withCount(['commentReactions as helpful_count' => fn ($query) => $query->where('helpful', true)])
            ->latest()
            ->get();

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
        $saved = $userId ? $posts->bookmarks()->where('user_id', $userId)->exists() : false;

        return view('layout.post-detail', [
            'post' => $posts,
            'averageRating' => $averageRating,
            'ratingsCount' => $ratingsCount,
            'auditsCount' => $auditsCount,
            'commentsCount' => $commentsCount,
            'ratingDistribution' => $ratingDistribution,
            'comments' => $comments,
            'commentUserRatings' => $commentUserRatings,
            'relatedPosts' => $relatedPosts,
            'userRating' => $userRating,
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
