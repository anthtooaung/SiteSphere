<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportsRequest;
use App\Http\Requests\UpdateReportsRequest;
use App\Models\Notificatioins;
use App\Models\Posts;
use App\Models\Reports;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

        $this->notifyAdminsAboutReport($request, $post);
        $this->flashSuccessToast($request);

        return back()->with('success', 'Post reported.');
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
            'timer' => 3000,
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
}
