<?php

namespace App\Http\Controllers;

use App\Models\AuditLogs;
use App\Models\Comments;
use App\Models\Notificatioins;
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

        DB::transaction(function () use ($comment): void {
            Ratings::query()
                ->where('user_id', $comment->user_id)
                ->where('post_id', $comment->post_id)
                ->delete();

            $comment->forceDelete();
        });

        return back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Permanently delete the specified comment (admin only).
     */
    public function delete(Request $request, Comments $comment): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);

        $reason = $request->input('reason', 'No reason provided');

        DB::transaction(function () use ($comment, $user, $reason): void {
            Ratings::query()
                ->where('user_id', $comment->user_id)
                ->where('post_id', $comment->post_id)
                ->delete();

            $comment->commentReactions()->delete();

            AuditLogs::query()->create([
                'user_id' => $user->id,
                'action' => 'delete_comment',
                'category' => 'moderation',
                'target_type' => Comments::class,
                'target_id' => $comment->id,
                'reason' => 'Comment permanently deleted by an admin. Reason: '.$reason,
            ]);

            Notificatioins::query()->create([
                'to_user_id' => $comment->user_id,
                'from_user_id' => $user->id,
                'target_type' => 'comments',
                'target_id' => $comment->id,
                'message' => 'Your comment was permanently deleted by an admin. Reason: '.$reason,
                'is_read' => false,
            ]);

            $comment->forceDelete();
        });

        return back()->with('success', 'Comment permanently deleted.');
    }

    public function forceDelete(Request $request, Comments $comment): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'admin', 403);
        abort_unless($comment->trashed(), 404);

        $reason = $request->input('reason', 'No reason provided');

        $comment->commentReactions()->delete();

        AuditLogs::query()->create([
            'user_id' => $user->id,
            'action' => 'force_delete_comment',
            'category' => 'moderation',
            'target_type' => Comments::class,
            'target_id' => $comment->id,
            'reason' => 'Comment permanently deleted by admin. Reason: '.$reason,
        ]);

        $comment->forceDelete();

        return back()->with('success', 'Comment permanently deleted.');
    }
}
