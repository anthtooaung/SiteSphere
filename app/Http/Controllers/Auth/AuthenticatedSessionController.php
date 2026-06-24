<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\LoginTwoFactorOtpMail;
use App\Models\OtpVerifications;
use App\Models\User;
use App\Traits\ChecksMailConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use SweetAlert2\Laravel\Swal;
use Throwable;

class AuthenticatedSessionController extends Controller
{
    use ChecksMailConfiguration;

    private const DEFAULT_TOAST_POSITION = 'top-end';

    private const TOAST_POSITIONS = [
        'top-start',
        'top-end',
        'bottom-end',
        'bottom-start',
    ];

    public const TWO_FACTOR_USER_ID_KEY = 'login.two_factor.user_id';

    public const TWO_FACTOR_REMEMBER_KEY = 'login.two_factor.remember';

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login-register')->with('isLogin', true);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        // Check if user is banned
        if ($user->isBanned()) {
            Auth::guard('web')->logout();

            $banMessage = 'Your account has been banned.';
            if ($user->ban_reason) {
                $banMessage .= ' Reason: '.$user->ban_reason;
            }

            return back()->withErrors([
                'email' => $banMessage,
            ]);
        }

        if ($user->two_factor_enabled) {
            $this->createAndSendTwoFactorOtp($user);

            $request->session()->put([
                self::TWO_FACTOR_USER_ID_KEY => $user->id,
                self::TWO_FACTOR_REMEMBER_KEY => $request->boolean('remember'),
            ]);

            return redirect()
                ->route('login.two-factor')
                ->with('status', 'We sent a login verification code to your email.');
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $this->flashSuccessToast(
            title: 'Signed in successfully',
            text: 'Welcome back to SiteSphere.',
            position: $this->toastPositionFor($request),
        );

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function toastPositionFor(Request $request): string
    {
        $position = $request->user()?->settings()->value('noti_location');

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

        Log::info("Login 2FA OTP verification code for {$user->email}: {$otpCode}");

        if (! $this->isMailConfigured()) {
            return;
        }

        try {
            Mail::to($user->email)->send(new LoginTwoFactorOtpMail($otpCode));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
