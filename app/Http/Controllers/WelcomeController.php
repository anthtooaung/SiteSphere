<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $mostReviewedPosts = Posts::query()
            ->select(['id', 'slug', 'url'])
            ->withCount([
                'userPosts as visible_reviews_count',
            ])
            ->withAvg('ratings as average_rating', 'rating')
            ->whereHas('userPosts')
            ->orderByDesc('visible_reviews_count')
            ->latest('id')
            ->take(3)
            ->get();

        return view('layout.welcome', [
            'mostReviewedPosts' => $mostReviewedPosts,
        ]);
    }
}
