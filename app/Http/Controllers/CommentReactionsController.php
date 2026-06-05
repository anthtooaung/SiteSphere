<?php

namespace App\Http\Controllers;

use App\Models\CommentReactions;
use App\Models\Comments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentReactionsController extends Controller
{
    public function toggle(Request $request, Comments $comment): JsonResponse
    {
        $user = $request->user();

        $reaction = CommentReactions::query()
            ->where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($reaction) {
            $reaction->delete();
            $voted = false;
        } else {
            CommentReactions::query()->create([
                'user_id' => $user->id,
                'comment_id' => $comment->id,
                'helpful' => true,
            ]);
            $voted = true;
        }

        $helpfulCount = CommentReactions::query()
            ->where('comment_id', $comment->id)
            ->where('helpful', true)
            ->count();

        return response()->json([
            'voted' => $voted,
            'helpful_count' => $helpfulCount,
        ]);
    }
}
