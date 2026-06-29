<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\CustomTags;
use App\Models\Posts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    private const SERVER_POST_LIMIT = 12;

    public function __invoke(Request $request): View|JsonResponse
    {
        $initialCategory = $request->query('category');
        $initialCategory = is_string($initialCategory) ? $initialCategory : null;
        $user = $request->user();
        $userId = $user?->id;

        $customTags = $user
            ? CustomTags::query()->where('user_id', $user->id)->get()->keyBy('tag_id')
            : collect();

        $postsQuery = Posts::query()
            ->with([
                'userPosts' => fn ($query) => $query
                    ->with('user.settings')
                    ->oldest(),
                'tags.categories',
                'reports' => fn ($query) => $query
                    ->where('user_id', $userId)
                    ->when(! $userId, fn ($query) => $query->whereRaw('1 = 0')),
            ])
            ->withAvg('ratings as average_rating', 'rating')
            ->withCount([
                'ratings',
                'comments',
                'bookmarks as is_bookmarked' => fn ($query) => $query
                    ->when($userId, fn ($query) => $query->where('user_id', $userId))
                    ->when(! $userId, fn ($query) => $query->whereRaw('1 = 0')),
            ])
            ->whereHas('userPosts');

        $posts = $postsQuery
            // Apply search filter
            ->when($request->query('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));
                if (empty($search)) {
                    return $query;
                }

                return $query->where(function ($q) use ($search) {
                    $q->where('url', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            // Apply category filter
            ->when($request->query('category'), function ($query) use ($request) {
                $categories = is_string($request->query('category')) ? explode(',', $request->query('category')) : $request->query('category');
                $categories = array_filter((array) $categories, fn ($c) => ! empty($c) && strtolower($c) !== 'all');

                if (empty($categories)) {
                    return $query;
                }

                return $query->whereHas('tags.categories', function ($q) use ($categories) {
                    $q->whereIn('slug', $categories);
                });
            })
            // Apply tags filter
            ->when($request->query('tags'), function ($query) use ($request) {
                $tags = is_string($request->query('tags')) ? explode(',', $request->query('tags')) : $request->query('tags');
                $tags = array_filter((array) $tags);

                if (empty($tags)) {
                    return $query;
                }

                return $query->whereHas('tags', function ($q) use ($tags) {
                    $q->whereIn('slug', $tags);
                });
            })
            // Apply rating filter
            ->when($request->query('rating'), function ($query) use ($request) {
                $rating = $request->query('rating');
                if (empty($rating) || strtolower((string) $rating) === 'all') {
                    return $query;
                }

                $minRating = (int) $rating;

                return $query->having('average_rating', '>=', $minRating);
            })
            // Apply sorting
            ->when($request->query('sort'), function ($query) use ($request) {
                $sort = $request->query('sort');
                if ($sort === 'rating' || $sort === 'best') {
                    return $query->orderByDesc('average_rating')->orderByDesc('ratings_count');
                }

                return $query->latest();
            }, function ($query) {
                return $query->latest();
            })
            ->paginate(self::SERVER_POST_LIMIT)
            ->through(function (Posts $post) use ($customTags): array {
                $primaryTag = $post->tags->first();
                $primaryCategory = $primaryTag?->categories->first();

                return [
                    'id' => $post->id,
                    'url' => $post->url,
                    'slug' => $post->slug,
                    'category' => $primaryCategory?->name ?? 'Uncategorized',
                    'category_slug' => $primaryCategory?->slug ?? 'uncategorized',
                    'tags' => $post->tags->map(function ($tag) use ($customTags): array {
                        $custom = $customTags->get($tag->id);

                        return [
                            'id' => $tag->id,
                            'name' => $custom ? $custom->name : $tag->name,
                            'color' => $custom ? $custom->color : $tag->tag_color,
                            'slug' => $tag->slug,
                        ];
                    })->values()->all(),
                    'average_rating' => round((float) ($post->average_rating ?? 0), 1),
                    'ratings_count' => (int) $post->ratings_count,
                    'comments_count' => (int) $post->comments_count,
                    'is_bookmarked' => (bool) $post->is_bookmarked,
                    'is_unsecure' => (bool) $post->is_unsecure,
                    'has_reported' => $post->reports->isNotEmpty(),
                    'profiles' => $post->userPosts
                        ->map(function ($userPost): array {
                            $user = $userPost->user;
                            $isProfileVisible = ! $userPost->user_hidden;
                            $name = $user?->name ?? 'Reviewer';

                            return [
                                'user_id' => ($isProfileVisible && $user) ? $user->id : null,
                                'username' => $isProfileVisible ? '@'.Str::slug($name, '_') : 'Anonymous',
                                'initial' => $isProfileVisible ? Str::of($name)->substr(0, 1)->upper()->toString() : '?',
                                'time' => 'Published '.$userPost->created_at->diffForHumans(),
                                'title' => $userPost->title,
                                'description' => $userPost->description,
                                'avatar' => $isProfileVisible ? $user?->getAvatarUrl() ?? '' : '',
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            });

        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.home-posts', ['posts' => $posts])->render(),
                'pagination' => $posts->withQueryString()->links()->render(),
                'hasMorePages' => $posts->hasMorePages(),
                'total' => $posts->total(),
                'currentPage' => $posts->currentPage(),
                'lastPage' => $posts->lastPage(),
            ]);
        }

        $categories = Categories::query()
            ->with(['tags' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('layout.home', [
            'posts' => $posts,
            'categories' => $categories,
            'categoryTags' => $categories
                ->mapWithKeys(fn (Categories $category): array => [
                    $category->slug => $category->tags->map(fn ($t) => $customTags->get($t->id)?->name ?? $t->name)->values(),
                ])
                ->put('all', $categories->flatMap->tags->map(fn ($t) => $customTags->get($t->id)?->name ?? $t->name)->unique()->values()),
            'categoryLabels' => $categories
                ->pluck('name', 'slug')
                ->put('All', 'All')
                ->put('all', 'All'),
            'initialCategory' => $initialCategory,
        ]);
    }
}
