<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use App\Models\Posts;
use App\Models\Ratings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Posts $posts): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $user = $request->user();

        // Prevent author from commenting
        if ($posts->userPosts()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'You cannot comment on your own post.']);
        }

        // Limit to one comment per post
        if (Comments::query()->where('user_id', $user->id)->where('post_id', $posts->id)->exists()) {
            return back()->withErrors(['error' => 'You have already commented on this post.']);
        }

        DB::transaction(function () use ($posts, $user, $validated): void {
            Comments::query()->create([
                'user_id' => $user->id,
                'post_id' => $posts->id,
                'content' => $validated['content'],
            ]);

            Ratings::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'post_id' => $posts->id,
                ],
                [
                    'rating' => $validated['rating'],
                ]
            );
        });

        return back()->with('success', 'Review submitted successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comments $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update($validated);

        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comments $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }
}
