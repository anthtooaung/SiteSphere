<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportsRequest;
use App\Http\Requests\UpdateReportsRequest;
use App\Models\Posts;
use App\Models\Reports;
use Illuminate\Http\RedirectResponse;

class ReportsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportsRequest $request, Posts $post): RedirectResponse
    {
        Reports::query()->create([
            'user_id' => $request->user()->id,
            'target_name' => 'posts',
            'target_id' => $post->id,
            'reason' => $request->validated('reason'),
            'admin_read' => false,
        ]);

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
}
