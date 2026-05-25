<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\OtpVerifications;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset OTP request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset OTP request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => $request->string('email')->lower()->toString(),
        ]);

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->string('email')->toString())->firstOrFail();
        $otpCode = (string) rand(100000, 999999);

        OtpVerifications::create([
            'user_id' => $user->id,
            'otp' => $otpCode,
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)->send(new PasswordResetOtpMail($otpCode));

        $request->session()->put([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
        ]);

        return redirect()
            ->route('password.reset')
            ->with('status', 'We sent a password reset OTP to your email address.');
    }
}
