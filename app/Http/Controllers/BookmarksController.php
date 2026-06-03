<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookmarksRequest;
use App\Models\Bookmarks;
use App\Models\Posts;
use Illuminate\Http\RedirectResponse;

class BookmarksController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookmarksRequest $request, Posts $post): RedirectResponse
    {
        $bookmark = Bookmarks::query()
            ->where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return back()->with('success', 'Post removed from saved posts.');
        }

        Bookmarks::query()->create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);

        return back()->with('success', 'Post saved.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bookmarks $bookmarks)
    {
        //
    }
}
