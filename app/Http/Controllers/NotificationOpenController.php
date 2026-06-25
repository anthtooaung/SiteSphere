<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use App\Models\Notificatioins;
use App\Models\Posts;
use App\Models\User;
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
            'posts' => $this->redirectToPost($notification->target_id),
            'comments' => $this->redirectToComment($notification->target_id),
            'user_posts' => $this->redirectToUserPost($notification->target_id),
            'users' => $this->redirectToProfile($notification->target_id),
            default => redirect()->route('home'),
        };
    }

    private function redirectToPost(int $postId): RedirectResponse
    {
        $post = Posts::find($postId);

        return $post
            ? redirect()->route('posts.show', ['posts' => $post->slug])
            : redirect()->route('home');
    }

    private function redirectToComment(int $commentId): RedirectResponse
    {
        $comment = Comments::find($commentId);

        return $comment && $comment->post
            ? redirect(route('posts.show', ['posts' => $comment->post->slug]) . '#comment-' . $comment->id)
            : redirect()->route('home');
    }

    private function redirectToUserPost(int $userPostId): RedirectResponse
    {
        $userPost = \App\Models\UserPosts::find($userPostId);

        return $userPost && $userPost->post
            ? redirect(route('posts.show', ['posts' => $userPost->post->slug]) . '#panel-user-' . $userPost->user_id)
            : redirect()->route('home');
    }

    private function redirectToProfile(int $userId): RedirectResponse
    {
        $user = User::find($userId);

        return $user
            ? redirect()->route('profile-detail', ['slug' => $user->slug])
            : redirect()->route('home');
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
