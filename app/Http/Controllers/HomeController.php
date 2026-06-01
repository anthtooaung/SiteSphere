<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Posts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $initialCategory = $request->query('category');
        $initialCategory = is_string($initialCategory) ? $initialCategory : null;

        $posts = Posts::query()
            ->with([
                'userPosts' => fn ($query) => $query
                    ->where('user_hidden', false)
                    ->whereHas('user.settings', fn ($settingsQuery) => $settingsQuery->where('user_post_visible', true))
                    ->with('user')
                    ->latest(),
                'tags.categories',
            ])
            ->withAvg('ratings as average_rating', 'rating')
            ->withCount([
                'ratings',
                'comments',
                'bookmarks as is_bookmarked' => fn ($query) => $query
                    ->where('user_id', $request->user()->id),
            ])
            ->whereHas('userPosts', fn ($query) => $query
                ->where('user_hidden', false)
                ->whereHas('user.settings', fn ($settingsQuery) => $settingsQuery->where('user_post_visible', true)))
            ->latest()
            ->get()
            ->map(function (Posts $post): array {
                $primaryTag = $post->tags->first();
                $primaryCategory = $primaryTag?->categories->first();

                return [
                    'title' => $post->title,
                    'url' => $post->url,
                    'category' => $primaryCategory?->name ?? 'Uncategorized',
                    'category_slug' => $primaryCategory?->slug ?? 'uncategorized',
                    'tags' => $post->tags->pluck('name')->values()->all(),
                    'average_rating' => round((float) ($post->average_rating ?? 0), 1),
                    'ratings_count' => (int) $post->ratings_count,
                    'comments_count' => (int) $post->comments_count,
                    'is_bookmarked' => (bool) $post->is_bookmarked,
                    'profiles' => $post->userPosts
                        ->map(function ($userPost): array {
                            $user = $userPost->user;
                            $name = $user?->name ?? 'Reviewer';

                            return [
                                'username' => '@'.Str::slug($name, '_'),
                                'initial' => Str::of($name)->substr(0, 1)->upper()->toString(),
                                'time' => 'Published '.$userPost->created_at->diffForHumans(),
                                'description' => $userPost->description,
                                'avatar' => $user?->getAvatarUrl() ?? '',
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
                    $category->slug => $category->tags->pluck('name')->values(),
                ])
                ->put('all', $categories->flatMap->tags->pluck('name')->unique()->values()),
            'categoryLabels' => $categories
                ->pluck('name', 'slug')
                ->put('All', 'All')
                ->put('all', 'All'),
            'initialCategory' => $initialCategory,
        ]);
    }
}
