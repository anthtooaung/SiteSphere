<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpVerifications;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_user_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'email' => $request->session()->get('password_reset_email'),
        ]);
    }

    /**
     * Verify the password reset OTP.
     *
     * @throws ValidationException
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('password_reset_user_id');

        if (! $userId) {
            throw ValidationException::withMessages([
                'otp_code' => 'Please request a new password reset OTP.',
            ]);
        }

        $verification = OtpVerifications::where('user_id', $userId)
            ->where('expire_at', '>', now())
            ->where('is_verified', false)
            ->orderBy('id', 'desc')
            ->first();

        if (! $verification || $verification->otp !== $request->otp_code) {
            throw ValidationException::withMessages([
                'otp_code' => 'The OTP is invalid or has expired.',
            ]);
        }

        $user = User::findOrFail($userId);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        $verification->update([
            'is_verified' => true,
        ]);

        $request->session()->put('password_reset_otp_verified', true);

        return redirect()->route('password.new');
    }

    /**
     * Display the new password view.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('password_reset_otp_verified')) {
            return redirect()->route('password.reset');
        }

        return view('auth.new-password', [
            'email' => $request->session()->get('password_reset_email'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->session()->get('password_reset_otp_verified')) {
            return redirect()->route('password.reset');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::findOrFail($request->session()->get('password_reset_user_id'));

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $request->session()->forget([
            'password_reset_user_id',
            'password_reset_email',
            'password_reset_otp_verified',
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Your password has been reset. Please sign in with your new password.');
    }
}
