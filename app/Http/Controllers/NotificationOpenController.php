<?php

namespace App\Http\Controllers;

use App\Models\Notificatioins;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationOpenController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Notificatioins $notification): RedirectResponse
    {
        $user = $request->user();

        abort_unless($notification->to_user_id === $user->id, 403);

        if (! $notification->is_read) {
            $notification->forceFill(['is_read' => true])->save();
        }

        // Redirect based on target type
        return match ($notification->target_type) {
            'posts' => redirect()->route('posts.show', ['posts' => $notification->target_id]),
            'comments' => redirect()->route('posts.show', ['posts' => $notification->target_id]),
            'users' => redirect()->route('profile-detail', ['slug' => $notification->target_id]),
            default => redirect()->route('home'),
        };
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $user = $request->user();

        Notificatioins::query()
            ->where('to_user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
