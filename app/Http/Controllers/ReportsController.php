<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportsRequest;
use App\Http\Requests\UpdateReportsRequest;
use App\Models\Comments;
use App\Models\Notificatioins;
use App\Models\Posts;
use App\Models\Reports;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SweetAlert2\Laravel\Swal;

class ReportsController extends Controller
{
    private const DEFAULT_TOAST_POSITION = 'top-end';

    private const TOAST_POSITIONS = [
        'top-start',
        'top-end',
        'bottom-end',
        'bottom-start',
    ];

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportsRequest $request, Posts $post): RedirectResponse
    {
        $validated = $request->validated();
        $reason = $this->reportReason(
            reason: $validated['reason'],
            details: $validated['details'] ?? null,
        );

        Reports::query()->create([
            'user_id' => $request->user()->id,
            'target_name' => 'posts',
            'target_id' => $post->id,
            'reason' => $reason,
            'admin_read' => false,
        ]);

        $post->increment('report_count');

        $this->notifyAdminsAboutReport($request, $post);
        $this->flashSuccessToast($request);

        return back()->with('success', 'Post reported.');
    }

    /**
     * Store a report for a comment.
     */
    public function storeForComment(Request $request, Comments $comment): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'details' => ['nullable', 'string', 'max:600'],
        ]);

        $reason = $this->reportReason(
            reason: $validated['reason'],
            details: $validated['details'] ?? null,
        );

        Reports::query()->create([
            'user_id' => $request->user()->id,
            'target_name' => 'comments',
            'target_id' => $comment->id,
            'reason' => $reason,
            'admin_read' => false,
        ]);

        $comment->increment('report_count');

        $this->notifyAdminsAboutCommentReport($request, $comment);

        Swal::fire([
            'toast' => true,
            'position' => $this->toastPositionForRequest($request),
            'showConfirmButton' => false,
            'timer' => 1000,
            'timerProgressBar' => true,
            'icon' => 'success',
            'title' => 'Report submitted',
            'text' => 'Thanks for helping us keep SiteSphere safe.',
            'didOpen' => '(toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }',
        ]);

        return back()->with('success', 'Comment reported.');
    }

    /**
     * Store a report for a user.
     */
    public function storeForUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'details' => ['nullable', 'string', 'max:600'],
        ]);

        $reason = $this->reportReason(
            reason: $validated['reason'],
            details: $validated['details'] ?? null,
        );

        Reports::query()->create([
            'user_id' => $request->user()->id,
            'target_name' => 'users',
            'target_id' => $user->id,
            'reason' => $reason,
            'admin_read' => false,
        ]);

        $user->increment('report_count');

        $this->notifyAdminsAboutUserReport($request, $user);

        Swal::fire([
            'toast' => true,
            'position' => $this->toastPositionForRequest($request),
            'showConfirmButton' => false,
            'timer' => 1000,
            'timerProgressBar' => true,
            'icon' => 'success',
            'title' => 'Report submitted',
            'text' => 'Thanks for helping us keep SiteSphere safe.',
            'didOpen' => '(toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }',
        ]);

        return back()->with('success', 'User reported.');
    }

    /**
     * Store a report for a user post (description).
     */
    public function storeForUserPost(Request $request, UserPosts $userPost): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'details' => ['nullable', 'string', 'max:600'],
        ]);

        $reason = $this->reportReason(
            reason: $validated['reason'],
            details: $validated['details'] ?? null,
        );

        Reports::query()->create([
            'user_id' => $request->user()->id,
            'target_name' => 'user_posts',
            'target_id' => $userPost->id,
            'reason' => $reason,
            'admin_read' => false,
        ]);

        $userPost->increment('report_count');

        $this->notifyAdminsAboutUserPostReport($request, $userPost);

        Swal::fire([
            'toast' => true,
            'position' => $this->toastPositionForRequest($request),
            'showConfirmButton' => false,
            'timer' => 1000,
            'timerProgressBar' => true,
            'icon' => 'success',
            'title' => 'Report submitted',
            'text' => 'Thanks for helping us keep SiteSphere safe.',
            'didOpen' => '(toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }',
        ]);

        return back()->with('success', 'Description reported.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reports $reports)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reports $reports)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportsRequest $request, Reports $reports)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reports $reports)
    {
        //
    }

    private function reportReason(string $reason, ?string $details): string
    {
        if (blank($details)) {
            return $reason;
        }

        return "{$reason}\n\nDetails: {$details}";
    }

    private function flashSuccessToast(StoreReportsRequest $request): void
    {
        Swal::fire([
            'toast' => true,
            'position' => $this->toastPositionFor($request),
            'showConfirmButton' => false,
            'timer' => 1000,
            'timerProgressBar' => true,
            'icon' => 'success',
            'title' => 'Report submitted',
            'text' => 'Thanks for helping us keep SiteSphere safe.',
            'didOpen' => '(toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }',
        ]);
    }

    private function toastPositionFor(StoreReportsRequest $request): string
    {
        $position = $request->user()?->settings()->value('noti_location');

        return in_array($position, self::TOAST_POSITIONS, true)
            ? $position
            : self::DEFAULT_TOAST_POSITION;
    }

    private function toastPositionForRequest(Request $request): string
    {
        $position = $request->user()?->settings()->value('noti_location');

        return in_array($position, self::TOAST_POSITIONS, true)
            ? $position
            : self::DEFAULT_TOAST_POSITION;
    }

    private function notifyAdminsAboutReport(StoreReportsRequest $request, Posts $post): void
    {
        $reporter = $request->user();
        $message = "{$reporter->name} reported post: {$post->title}";

        User::query()
            ->where('role', 'admin')
            ->select('id')
            ->get()
            ->each(function (User $admin) use ($reporter, $post, $message): void {
                Notificatioins::query()->create([
                    'to_user_id' => $admin->id,
                    'from_user_id' => $reporter->id,
                    'target_type' => 'posts',
                    'target_id' => $post->id,
                    'message' => $message,
                    'is_read' => false,
                ]);
            });
    }

    private function notifyAdminsAboutCommentReport(Request $request, Comments $comment): void
    {
        $reporter = $request->user();
        $message = "{$reporter->name} reported a comment by {$comment->user->name}";

        User::query()
            ->where('role', 'admin')
            ->select('id')
            ->get()
            ->each(function (User $admin) use ($reporter, $comment, $message): void {
                Notificatioins::query()->create([
                    'to_user_id' => $admin->id,
                    'from_user_id' => $reporter->id,
                    'target_type' => 'comments',
                    'target_id' => $comment->id,
                    'message' => $message,
                    'is_read' => false,
                ]);
            });
    }

    private function notifyAdminsAboutUserReport(Request $request, User $reportedUser): void
    {
        $reporter = $request->user();
        $message = "{$reporter->name} reported user: {$reportedUser->name}";

        User::query()
            ->where('role', 'admin')
            ->select('id')
            ->get()
            ->each(function (User $admin) use ($reporter, $reportedUser, $message): void {
                Notificatioins::query()->create([
                    'to_user_id' => $admin->id,
                    'from_user_id' => $reporter->id,
                    'target_type' => 'users',
                    'target_id' => $reportedUser->id,
                    'message' => $message,
                    'is_read' => false,
                ]);
            });
    }

    private function notifyAdminsAboutUserPostReport(Request $request, UserPosts $userPost): void
    {
        $reporter = $request->user();
        $postTitle = $userPost->post?->title ?? 'Unknown Post';
        $message = "{$reporter->name} reported a description on post: {$postTitle}";

        User::query()
            ->where('role', 'admin')
            ->select('id')
            ->get()
            ->each(function (User $admin) use ($reporter, $userPost, $message): void {
                Notificatioins::query()->create([
                    'to_user_id' => $admin->id,
                    'from_user_id' => $reporter->id,
                    'target_type' => 'posts',
                    'target_id' => $userPost->post_id,
                    'message' => $message,
                    'is_read' => false,
                ]);
            });
    }
}
