<?php

namespace App\Http\Controllers;

use App\Models\Bookmarks;
use App\Models\CustomTags;
use App\Models\Posts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SavedPostsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = Str::of((string) $request->query('search'))->trim()->toString();
        $sort = in_array($request->query('sort'), ['recent', 'az'], true)
            ? (string) $request->query('sort')
            : 'recent';
        $startDate = Str::of((string) $request->query('start_date'))->trim()->toString();
        $endDate = Str::of((string) $request->query('end_date'))->trim()->toString();

        $user = $request->user();
        $totalSavedCount = Bookmarks::query()
            ->where('user_id', $user->id)
            ->whereHas('post.userPosts')
            ->count();

        $customTags = $user
            ? CustomTags::query()->where('user_id', $user->id)->get()->keyBy('tag_id')
            : collect();

        $bookmarks = Bookmarks::query()
            ->where('user_id', $user->id)
            ->whereHas('post.userPosts')
            ->when($search !== '', fn ($query) => $query->whereHas('post', fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('url', 'like', "%{$search}%")))
            ->when($startDate !== '', fn ($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate !== '', fn ($query) => $query->whereDate('created_at', '<=', $endDate))
            ->with([
                'post' => fn ($query) => $query
                    ->with([
                        'userPosts' => fn ($query) => $query
                            ->with('user.settings')
                            ->latest(),
                        'tags.categories',
                    ])
                    ->withAvg('ratings as average_rating', 'rating')
                    ->withCount(['ratings', 'comments']),
            ])
            ->latest()
            ->get()
            ->when($sort === 'az', fn ($bookmarks) => $bookmarks->sortBy(fn (Bookmarks $bookmark): string => Str::lower($bookmark->post->title)));

        return view('layout.menu.saved-post', [
            'savedPosts' => $bookmarks
                ->map(fn (Bookmarks $bookmark): array => $this->formatSavedPost($bookmark, $customTags))
                ->values(),
            'savedPostFilters' => [
                'search' => $search,
                'sort' => $sort,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'totalSavedCount' => $totalSavedCount,
        ]);
    }

    /**
     * @param  Collection<int, CustomTags>  $customTags
     * @return array<string, mixed>
     */
    private function formatSavedPost(Bookmarks $bookmark, Collection $customTags): array
    {
        /** @var Posts $post */
        $post = $bookmark->post;
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
            'is_bookmarked' => true,
            'saved_at' => $bookmark->created_at?->toDateString() ?? '',
            'saved_at_label' => $bookmark->created_at?->diffForHumans() ?? '',
            'profiles' => $post->userPosts
                ->map(function ($userPost): array {
                    $user = $userPost->user;
                    $isProfileVisible = ! $userPost->user_hidden;
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
    }
}
