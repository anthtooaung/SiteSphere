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
        $postIds = UserPosts::where('user_id', $user->id)->where('user_hidden', false)->pluck('post_id');
        $uploadsCount = $postIds->count();
        $averageRating = Ratings::whereIn('post_id', $postIds)->avg('rating') ?: 0.0;
        $averageRating = round((float) $averageRating, 1);

        $role = $user->role === 'admin' ? 'Admin' : 'Reviewer';

        return view('components.layout.hover-profile-card', [
            'cardUser' => $user,
            'uploadsCount' => $uploadsCount,
            'averageRating' => $averageRating,
            'role' => $role,
            'maskedEmail' => $this->maskEmail($user->email),
        ]);
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $local = $parts[0];
        $domain = $parts[1];

        if (strlen($local) <= 4) {
            return str_repeat('*', strlen($local) - 1) . substr($local, -1) . '@' . $domain;
        }

        return substr($local, 0, 2) . str_repeat('*', strlen($local) - 4) . substr($local, -2) . '@' . $domain;
    }
}
