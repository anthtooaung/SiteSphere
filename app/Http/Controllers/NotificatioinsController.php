<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificatioinsRequest;
use App\Http\Requests\UpdateNotificatioinsRequest;
use App\Models\Notificatioins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificatioinsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $filter = $request->input('filter', 'all');
        $search = $request->input('search', '');

        $query = Notificatioins::query()
            ->where('to_user_id', $user->id);

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        }

        if ($search !== '') {
            $query->where('message', 'like', '%' . $search . '%');
        }

        $notifications = $query->latest()->paginate(20)->withQueryString();
        $unreadCount = Notificatioins::where('to_user_id', $user->id)->where('is_read', false)->count();

        return view('layout.menu.notifications', compact(
            'notifications',
            'unreadCount',
            'filter',
            'search',
        ));
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
    public function store(StoreNotificatioinsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Notificatioins $notificatioins)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notificatioins $notificatioins)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificatioinsRequest $request, Notificatioins $notificatioins)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notificatioins $notificatioins)
    {
        if ($notificatioins->to_user_id !== Auth::id()) {
            abort(403);
        }

        $notificatioins->delete();

        return redirect()->route('notifications.index')->with('status', 'notification-deleted');
    }
}
