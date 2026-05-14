<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserPostsRequest;
use App\Http\Requests\UpdateUserPostsRequest;
use App\Models\UserPosts;

class UserPostsController extends Controller
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
    public function store(StoreUserPostsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(UserPosts $userPosts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserPosts $userPosts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserPostsRequest $request, UserPosts $userPosts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserPosts $userPosts)
    {
        //
    }
}
