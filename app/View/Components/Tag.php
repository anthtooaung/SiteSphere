<?php

namespace App\View\Components;

use App\Models\CustomTags;
use App\Models\Tags;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tag extends Component
{
    public string $name;

    public string $color;

    /**
     * Cache for user's custom tags to prevent N+1 queries.
     *
     * @var array{user_id: int, tags: array<int, CustomTags>}|null
     */
    private static ?array $customTagsCache = null;

    /**
     * Retrieve all custom tags for the authenticated user and cache them for the request.
     *
     * @return array<int, CustomTags>
     */
    private static function getCustomTagsForUser(int $userId): array
    {
        if (self::$customTagsCache === null || self::$customTagsCache['user_id'] !== $userId) {
            self::$customTagsCache = [
                'user_id' => $userId,
                'tags' => CustomTags::query()
                    ->where('user_id', $userId)
                    ->get()
                    ->keyBy('tag_id')
                    ->all(),
            ];
        }

        return self::$customTagsCache['tags'];
    }

    /**
     * Create a new component instance.
     */
    public function __construct(public mixed $tag)
    {
        if (is_array($tag) && isset($tag['name']) && isset($tag['color'])) {
            $this->name = (string) $tag['name'];
            $this->color = (string) $tag['color'];

            return;
        }

        $resolvedTag = null;

        if ($tag instanceof Tags) {
            $resolvedTag = $tag;
        } elseif (is_string($tag)) {
            $resolvedTag = Tags::query()
                ->where('name', $tag)
                ->orWhere('slug', $tag)
                ->first();
        } elseif (is_array($tag)) {
            $tagId = $tag['id'] ?? null;
            if ($tagId !== null) {
                $resolvedTag = Tags::query()->find($tagId);
            } else {
                $tagName = $tag['name'] ?? null;
                if ($tagName !== null) {
                    $resolvedTag = Tags::query()
                        ->where('name', $tagName)
                        ->orWhere('slug', $tagName)
                        ->first();
                }
            }
        }

        if ($resolvedTag instanceof Tags) {
            $user = auth()->user();
            $customTag = null;

            if ($user !== null) {
                $customTags = self::getCustomTagsForUser($user->id);
                $customTag = $customTags[$resolvedTag->id] ?? null;
            }

            $this->name = $customTag instanceof CustomTags ? $customTag->name : $resolvedTag->name;
            $this->color = $customTag instanceof CustomTags ? $customTag->color : $resolvedTag->tag_color;
        } else {
            // Fallback for cases where tag details are passed directly or not found in DB
            if (is_array($tag)) {
                $this->name = (string) ($tag['name'] ?? 'Unknown');
                $this->color = (string) ($tag['color'] ?? '#6C5CE7');
            } else {
                $this->name = is_string($tag) ? $tag : 'Unknown';
                $this->color = '#6C5CE7';
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.tag');
    }
}
