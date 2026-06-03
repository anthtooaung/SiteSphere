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

        abort_unless($user?->role === 'admin', 403);
        abort_unless($notification->to_user_id === $user->id, 403);

        if (! $notification->is_read) {
            $notification->forceFill(['is_read' => true])->save();
        }

        return redirect()->route('reports', [
            'report' => $notification->target_id,
        ]);
    }
}
