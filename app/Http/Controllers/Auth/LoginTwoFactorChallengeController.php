<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginTwoFactorOtpMail;
use App\Models\OtpVerifications;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SweetAlert2\Laravel\Swal;
use Throwable;

class LoginTwoFactorChallengeController extends Controller
{
    private const DEFAULT_TOAST_POSITION = 'top-end';

    private const TOAST_POSITIONS = [
        'top-start',
        'top-end',
        'bottom-end',
        'bottom-start',
    ];

    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.login-two-factor', [
            'email' => $user->email,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $user = $this->pendingUser($request);

        if (! $user) {
            throw ValidationException::withMessages([
                'otp_code' => 'Please sign in again before verifying an OTP.',
            ]);
        }

        $verification = OtpVerifications::query()
            ->where('user_id', $user->id)
            ->where('expire_at', '>', now())
            ->where('is_verified', false)
            ->orderByDesc('id')
            ->first();

        if (! $verification || $verification->otp !== $request->string('otp_code')->toString()) {
            throw ValidationException::withMessages([
                'otp_code' => 'The OTP is invalid or has expired.',
            ]);
        }

        $verification->update([
            'is_verified' => true,
        ]);

        Auth::guard('web')->login($user, (bool) $request->session()->get(AuthenticatedSessionController::TWO_FACTOR_REMEMBER_KEY, false));

        $request->session()->regenerate();
        $this->clearPendingLogin($request);

        $this->flashSuccessToast(
            title: 'Signed in successfully',
            text: 'Welcome back to SiteSphere.',
            position: $this->toastPositionFor($user),
        );

        return redirect()->intended(route('home', absolute: false));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $this->createAndSendTwoFactorOtp($user);

        return back()->with('status', 'A new login verification code was sent to your email.');
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get(AuthenticatedSessionController::TWO_FACTOR_USER_ID_KEY);

        if (! $userId) {
            return null;
        }

        return User::query()
            ->whereKey($userId)
            ->where('two_factor_enabled', true)
            ->first();
    }

    private function clearPendingLogin(Request $request): void
    {
        $request->session()->forget([
            AuthenticatedSessionController::TWO_FACTOR_USER_ID_KEY,
            AuthenticatedSessionController::TWO_FACTOR_REMEMBER_KEY,
        ]);
    }

    private function createAndSendTwoFactorOtp(User $user): void
    {
        $otpCode = (string) rand(100000, 999999);

        OtpVerifications::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otpCode,
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        Log::info("Resent login 2FA OTP verification code for {$user->email}: {$otpCode}");

        $mailPassword = (string) config('mail.mailers.smtp.password');

        if ($mailPassword === '' || Str::contains($mailPassword, 'replace-with-gmail-app-password')) {
            return;
        }

        try {
            Mail::to($user->email)->send(new LoginTwoFactorOtpMail($otpCode));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function toastPositionFor(User $user): string
    {
        $position = $user->settings()->value('noti_location');

        return in_array($position, self::TOAST_POSITIONS, true)
            ? $position
            : self::DEFAULT_TOAST_POSITION;
    }

    private function flashSuccessToast(string $title, string $text, string $position): void
    {
        Swal::fire([
            'toast' => true,
            'position' => $position,
            'showConfirmButton' => false,
            'timer' => 1000,
            'timerProgressBar' => true,
            'icon' => 'success',
            'title' => $title,
            'text' => $text,
            'didOpen' => '(toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }',
        ]);
    }
}
