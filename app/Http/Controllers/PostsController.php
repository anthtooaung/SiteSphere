<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostsRequest;
use App\Http\Requests\UpdatePostsRequest;
use App\Models\AuditLogs;
use App\Models\Categories;
use App\Models\Posts;
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
                'user_hidden' => false,
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

        UserPosts::query()
            ->where('post_id', $post->id)
            ->update(['user_hidden' => true]);

        AuditLogs::query()->create([
            'user_id' => $user->id,
            'action' => 'ban_post',
            'target_type' => Posts::class,
            'target_id' => $post->id,
            'reason' => 'Post hidden from the home feed by an admin.',
        ]);

        return redirect()
            ->route('home')
            ->with('success', 'Post banned.');
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
    public function show(Posts $posts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Posts $posts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostsRequest $request, Posts $posts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Posts $posts)
    {
        //
    }
}
