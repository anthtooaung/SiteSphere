<?php

namespace App\Http\Controllers;

use App\Models\Ratings;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserHoverCardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user): View
    {
        // Calculate stats
        $uploadsCount = UserPosts::where('user_id', $user->id)->count();

        $postIds = UserPosts::where('user_id', $user->id)->pluck('post_id');
        $averageRating = Ratings::whereIn('post_id', $postIds)->avg('rating') ?: 0.0;
        $averageRating = round((float) $averageRating, 1);

        $role = $user->role === 'admin' ? 'Admin' : 'Reviewer';

        return view('components.layout.hover-profile-card', [
            'cardUser' => $user,
            'uploadsCount' => $uploadsCount,
            'averageRating' => $averageRating,
            'role' => $role,
        ]);
    }
}
