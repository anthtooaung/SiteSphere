<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEditTagsRequest;
use App\Models\AuditLogs;
use App\Models\Categories;
use App\Models\CustomTags;
use App\Models\Tags;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EditTagsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $categories = Categories::query()
            ->with(['tags' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        $customTags = CustomTags::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('tag_id');

        return view('layout.menu.edit-tag', [
            'isAdminTagEditor' => $user->role === 'admin',
            'tagSummary' => [
                'categories' => $categories->count(),
                'tags' => $categories->flatMap->tags->unique('id')->count(),
                'custom' => $customTags->count(),
            ],
            'taxonomy' => $this->taxonomy($categories, $customTags, $user->role === 'admin'),
        ]);
    }

    public function update(UpdateEditTagsRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        if ($user->role === 'admin') {
            $this->updateGlobalTaxonomy($request->taxonomyPayload(), $user);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tag defaults published for users.',
                ]);
            }

            return back()->with('success', 'Tag defaults published for users.');
        }

        $this->updateUserOverrides($request->taxonomyPayload(), $user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your tag styles were saved.',
            ]);
        }

        return back()->with('success', 'Your tag styles were saved.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        CustomTags::query()
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Your tag styles were reset to defaults.');
    }

    /**
     * @param  Collection<int, Categories>  $categories
     * @param  Collection<int, CustomTags>  $customTags
     * @return array<int, array<string, mixed>>
     */
    private function taxonomy(Collection $categories, Collection $customTags, bool $isAdmin): array
    {
        return $categories
            ->map(function (Categories $category) use ($customTags, $isAdmin): array {
                $categoryColor = $category->category_color ?? $category->tags->first()?->tag_color ?? '#6c5ce7';

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'color' => $this->normalizeColor($categoryColor),
                    'tags' => $category->tags
                        ->unique('id')
                        ->values()
                        ->map(function (Tags $tag) use ($customTags, $isAdmin): array {
                            $customTag = $customTags->get($tag->id);

                            return [
                                'id' => $tag->id,
                                'name' => $isAdmin ? $tag->name : ($customTag?->name ?? $tag->name),
                                'slug' => $tag->slug,
                                'color' => $this->normalizeColor($isAdmin ? $tag->tag_color : ($customTag?->color ?? $tag->tag_color)),
                                'default_name' => $tag->name,
                                'default_color' => $this->normalizeColor($tag->tag_color),
                            ];
                        })
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $taxonomy
     */
    private function updateUserOverrides(array $taxonomy, User $user): void
    {
        $tags = Tags::query()
            ->whereIn('id', $this->payloadTagIds($taxonomy))
            ->get()
            ->keyBy('id');

        $tagIdsToDelete = [];
        $tagsToUpdate = [];

        foreach ($taxonomy as $category) {
            foreach (($category['tags'] ?? []) as $payloadTag) {
                $tagId = (int) ($payloadTag['id'] ?? 0);
                $tag = $tags->get($tagId);

                if (! $tag instanceof Tags) {
                    continue;
                }

                $name = trim((string) $payloadTag['name']);
                $color = $this->normalizeColor((string) $payloadTag['color']);

                if ($name === $tag->name && $color === $this->normalizeColor($tag->tag_color)) {
                    $tagIdsToDelete[] = $tag->id;

                    continue;
                }

                $tagsToUpdate[] = [
                    'user_id' => $user->id,
                    'tag_id' => $tag->id,
                    'name' => $name,
                    'color' => $color,
                ];
            }
        }

        // Batch delete tags that match defaults
        if (! empty($tagIdsToDelete)) {
            CustomTags::query()
                ->where('user_id', $user->id)
                ->whereIn('tag_id', $tagIdsToDelete)
                ->delete();
        }

        // Batch upsert custom tags
        if (! empty($tagsToUpdate)) {
            foreach ($tagsToUpdate as $data) {
                CustomTags::query()->updateOrCreate(
                    [
                        'user_id' => $data['user_id'],
                        'tag_id' => $data['tag_id'],
                    ],
                    [
                        'name' => $data['name'],
                        'color' => $data['color'],
                    ],
                );
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $taxonomy
     */
    private function updateGlobalTaxonomy(array $taxonomy, User $admin): void
    {
        DB::transaction(function () use ($taxonomy, $admin): void {
            $currentCategories = Categories::query()->with('tags')->get()->keyBy('id');
            $incomingCategoryIds = collect($taxonomy)
                ->pluck('id')
                ->filter()
                ->map(fn ($id): int => (int) $id);

            $currentCategories
                ->reject(fn (Categories $category): bool => $incomingCategoryIds->contains($category->id))
                ->each(function (Categories $category): void {
                    $this->deleteCategoryOrFail($category);
                });

            foreach ($taxonomy as $payloadCategory) {
                $category = $this->persistCategory($payloadCategory);
                $incomingTagIds = collect($payloadCategory['tags'] ?? [])
                    ->pluck('id')
                    ->filter()
                    ->map(fn ($id): int => (int) $id);
                $currentTagIds = $category->exists
                    ? $category->tags()->pluck('tags.id')
                    : collect();

                $tagsToRemove = $currentTagIds->diff($incomingTagIds);

                if ($tagsToRemove->isNotEmpty()) {
                    // Batch detach all tags from this category
                    $category->tags()->detach($tagsToRemove);

                    // Find orphaned tags (no categories) using a single batch query
                    $orphanedTagIds = Tags::query()
                        ->whereIn('id', $tagsToRemove)
                        ->whereDoesntHave('categories')
                        ->pluck('id')
                        ->toArray();

                    if (! empty($orphanedTagIds)) {
                        Tags::query()->whereIn('id', $orphanedTagIds)->delete();
                    }
                }

                $syncIds = [];

                foreach (($payloadCategory['tags'] ?? []) as $payloadTag) {
                    $tag = $this->persistTag($payloadTag);
                    $syncIds[] = $tag->id;
                }

                $category->tags()->sync($syncIds);
            }

            AuditLogs::query()->create([
                'user_id' => $admin->id,
                'action' => 'update_tag_taxonomy',
                'category' => 'announcement',
                'target_type' => Categories::class,
                'target_id' => 0,
                'reason' => 'Published global category and tag defaults for users.',
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $payloadCategory
     */
    private function persistCategory(array $payloadCategory): Categories
    {
        $category = Categories::query()->find((int) ($payloadCategory['id'] ?? 0)) ?? new Categories;
        $name = trim((string) $payloadCategory['name']);

        $category->forceFill([
            'name' => $name,
            'slug' => $this->uniqueSlug(Categories::class, $name, $category->id),
            'category_color' => $this->normalizeColor((string) $payloadCategory['color']),
        ])->save();

        return $category;
    }

    /**
     * @param  array<string, mixed>  $payloadTag
     */
    private function persistTag(array $payloadTag): Tags
    {
        $tag = Tags::query()->find((int) ($payloadTag['id'] ?? 0)) ?? new Tags;
        $name = trim((string) $payloadTag['name']);

        $tag->forceFill([
            'name' => $name,
            'slug' => $this->uniqueSlug(Tags::class, $name, $tag->id),
            'tag_color' => $this->normalizeColor((string) $payloadTag['color']),
        ])->save();

        return $tag;
    }

    private function deleteCategoryOrFail(Categories $category): void
    {
        $category->loadMissing('tags');

        $tagIds = $category->tags->pluck('id')->toArray();

        if (! empty($tagIds)) {
            // Batch detach all tags from this category
            $category->tags()->detach();

            // Find orphaned tags (no categories) using a single batch query
            $orphanedTagIds = Tags::query()
                ->whereIn('id', $tagIds)
                ->whereDoesntHave('categories')
                ->pluck('id')
                ->toArray();

            if (! empty($orphanedTagIds)) {
                Tags::query()->whereIn('id', $orphanedTagIds)->delete();
            }
        }

        $category->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $taxonomy
     * @return array<int, int>
     */
    private function payloadTagIds(array $taxonomy): array
    {
        return collect($taxonomy)
            ->flatMap(fn (array $category): array => $category['tags'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  class-string<Categories|Tags>  $modelClass
     */
    private function uniqueSlug(string $modelClass, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'item';
        $slug = $baseSlug;
        $counter = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function normalizeColor(?string $color): string
    {
        $color = trim((string) $color);

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1 ? strtoupper($color) : '#6C5CE7';
    }
}
