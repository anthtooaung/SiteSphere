<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookmarksRequest;
use App\Http\Requests\UpdateBookmarksRequest;
use App\Models\Bookmarks;

class BookmarksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookmarksRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Bookmarks $bookmarks)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bookmarks $bookmarks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookmarksRequest $request, Bookmarks $bookmarks)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bookmarks $bookmarks)
    {
        //
    }
}
