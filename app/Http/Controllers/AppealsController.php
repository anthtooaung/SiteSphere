<?php

namespace App\Http\Controllers;

use App\Mail\AppealMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AppealsController extends Controller
{
    /**
     * Show the appeal form for banned users.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        if (! $user->isBanned()) {
            return redirect()->route('home');
        }

        return view('appeal', [
            'user' => $user,
        ]);
    }

    /**
     * Submit a ban appeal.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        abort_unless($user->isBanned(), 403);

        // Prevent spamming appeals (one per 24 hours)
        if ($user->appeal_submitted_at && $user->appeal_submitted_at->diffInHours(now()) < 24) {
            return back()->withErrors([
                'reason' => 'You can only submit one appeal every 24 hours. Please try again later.',
            ]);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        // Update user's appeal timestamp
        $user->forceFill([
            'appeal_submitted_at' => now(),
        ])->save();

        // Send email to all admins
        $admins = User::query()->where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new AppealMail($user, $validated['reason']));
        }

        // Log the user out so they cannot navigate freely
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome')->with('appeal_submitted', true);
    }
}
