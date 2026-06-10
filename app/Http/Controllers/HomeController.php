<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\CustomTags;
use App\Models\Posts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    private const SERVER_POST_LIMIT = 24;

    public function __invoke(Request $request): View
    {
        $initialCategory = $request->query('category');
        $initialCategory = is_string($initialCategory) ? $initialCategory : null;
        $user = $request->user();
        $userId = $user?->id;

        $customTags = $user
            ? CustomTags::query()->where('user_id', $user->id)->get()->keyBy('tag_id')
            : collect();

        $posts = Posts::query()
            ->with([
                'userPosts' => fn ($query) => $query
                    ->where('user_hidden', false)
                    ->with('user.settings')
                    ->latest(),
                'tags.categories',
            ])
            ->withAvg('ratings as average_rating', 'rating')
            ->withCount([
                'ratings',
                'comments',
                'bookmarks as is_bookmarked' => fn ($query) => $query
                    ->when($userId, fn ($query) => $query->where('user_id', $userId))
                    ->when(! $userId, fn ($query) => $query->whereRaw('1 = 0')),
            ])
            ->whereHas('userPosts', fn ($query) => $query
                ->where('user_hidden', false))
            ->latest()
            ->limit(self::SERVER_POST_LIMIT)
            ->get()
            ->map(function (Posts $post) use ($customTags): array {
                $primaryTag = $post->tags->first();
                $primaryCategory = $primaryTag?->categories->first();

                return [
                    'id' => $post->id,
                    'title' => $post->title,
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
                    'profiles' => $post->userPosts
                        ->map(function ($userPost): array {
                            $user = $userPost->user;
                            $isProfileVisible = (bool) $user?->settings?->user_post_visible;
                            $name = $user?->name ?? 'Reviewer';

                            return [
                                'user_id' => $user?->id,
                                'username' => $isProfileVisible ? '@'.Str::slug($name, '_') : 'Anonymous',
                                'initial' => $isProfileVisible ? Str::of($name)->substr(0, 1)->upper()->toString() : '?',
                                'time' => 'Published '.$userPost->created_at->diffForHumans(),
                                'description' => $userPost->description,
                                'avatar' => $isProfileVisible ? $user?->getAvatarUrl() ?? '' : '',
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            });

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
