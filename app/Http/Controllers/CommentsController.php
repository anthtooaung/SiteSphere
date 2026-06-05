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
}
