<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\OtpVerifications;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use SweetAlert2\Laravel\Swal;
use Throwable;

class RegisteredUserController extends Controller
{
    private const PENDING_REGISTRATION_KEY = 'registration.pending';

    private const REGISTRATION_OTP_VERIFIED_KEY = 'registration.otp_verified';

    private const DEFAULT_TOAST_POSITION = 'top-end';

    private const TOAST_POSITIONS = [
        'top-start',
        'top-end',
        'bottom-end',
        'bottom-start',
    ];

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.login-register')->with('isLogin', false);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        return redirect(route('register', absolute: false))
            ->withErrors([
                'email' => 'Please complete OTP verification before creating an account.',
            ]);
    }

    /**
     * Initiate registration and generate OTP.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $request->session()->put(self::PENDING_REGISTRATION_KEY, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $request->session()->forget(self::REGISTRATION_OTP_VERIFIED_KEY);

        $otpCode = (string) rand(100000, 999999);

        OtpVerifications::create([
            'email' => $validated['email'],
            'otp' => $otpCode,
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        Log::info("OTP verification code for {$validated['email']}: {$otpCode}");

        $otpDeliveryFailed = false;
        $deliveryMessage = null;
        $mailPassword = (string) config('mail.mailers.smtp.password');

        if ($mailPassword === '' || Str::contains($mailPassword, 'replace-with-gmail-app-password')) {
            return response()->json([
                'success' => true,
                'email' => $validated['email'],
                'otp_delivery_failed' => true,
                'message' => 'OTP created, but email is not configured. Set a real Gmail App Password in MAIL_PASSWORD and try Resend OTP.',
            ]);
        }

        try {
            Mail::to($validated['email'])->send(new OtpVerificationMail($otpCode));
        } catch (Throwable $exception) {
            $otpDeliveryFailed = true;
            $deliveryMessage = $exception->getMessage();

            report($exception);
        }

        return response()->json([
            'success' => true,
            'email' => $validated['email'],
            'otp_delivery_failed' => $otpDeliveryFailed,
            'message' => $otpDeliveryFailed
                ? 'OTP created, but email delivery failed. '.$deliveryMessage
                : null,
        ]);
    }

    /**
     * Resend OTP verification code.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $pendingRegistration = $this->pendingRegistration($request);

        if (! $pendingRegistration) {
            return response()->json([
                'message' => 'Please start registration before requesting a new OTP.',
            ], 422);
        }

        if (User::where('email', $pendingRegistration['email'])->exists()) {
            return response()->json([
                'message' => 'The email has already been taken.',
            ], 422);
        }

        $otpCode = (string) rand(100000, 999999);

        OtpVerifications::create([
            'email' => $pendingRegistration['email'],
            'otp' => $otpCode,
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        $request->session()->forget(self::REGISTRATION_OTP_VERIFIED_KEY);

        Log::info("Resent OTP verification code for {$pendingRegistration['email']}: {$otpCode}");

        $otpDeliveryFailed = false;
        $deliveryMessage = null;
        $mailPassword = (string) config('mail.mailers.smtp.password');

        if ($mailPassword === '' || Str::contains($mailPassword, 'replace-with-gmail-app-password')) {
            return response()->json([
                'success' => true,
                'otp_delivery_failed' => true,
                'message' => 'A new OTP was created, but email is not configured. Set a real Gmail App Password in MAIL_PASSWORD and try again.',
            ]);
        }

        try {
            Mail::to($pendingRegistration['email'])->send(new OtpVerificationMail($otpCode));
        } catch (Throwable $exception) {
            $otpDeliveryFailed = true;
            $deliveryMessage = $exception->getMessage();

            report($exception);
        }

        return response()->json([
            'success' => true,
            'otp_delivery_failed' => $otpDeliveryFailed,
            'message' => $otpDeliveryFailed
                ? 'A new OTP was created, but email delivery failed. '.$deliveryMessage
                : null,
        ]);
    }

    /**
     * Verify the sent OTP code.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $pendingRegistration = $this->pendingRegistration($request);

        if (! $pendingRegistration) {
            return response()->json([
                'message' => 'Please start registration before verifying an OTP.',
                'errors' => [
                    'otp_code' => ['Please request a new OTP.'],
                ],
            ], 422);
        }

        $verification = OtpVerifications::whereNull('user_id')
            ->where('email', $pendingRegistration['email'])
            ->where('expire_at', '>', now())
            ->where('is_verified', false)
            ->orderBy('id', 'desc')
            ->first();

        if (! $verification || $verification->otp !== $request->otp_code) {
            return response()->json([
                'message' => 'The provided OTP is invalid or has expired.',
                'errors' => [
                    'otp_code' => ['The OTP is invalid or has expired.'],
                ],
            ], 422);
        }

        $verification->update([
            'is_verified' => true,
        ]);

        $request->session()->put(self::REGISTRATION_OTP_VERIFIED_KEY, true);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Finalize registration and save optional profile info.
     */
    public function finalize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_dob' => ['nullable', 'date'],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'user_bio' => ['nullable', 'string', 'max:1000'],
            'user_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $pendingRegistration = $this->pendingRegistration($request);

        if (! $pendingRegistration || ! $request->session()->get(self::REGISTRATION_OTP_VERIFIED_KEY)) {
            return response()->json([
                'message' => 'OTP verification must be completed first.',
            ], 403);
        }

        if (User::where('email', $pendingRegistration['email'])->exists()) {
            return response()->json([
                'message' => 'The email has already been taken.',
                'errors' => [
                    'email' => ['The email has already been taken.'],
                ],
            ], 422);
        }

        $profileImagePath = null;
        if ($request->hasFile('user_image')) {
            $profileImagePath = $request->file('user_image')->store('profile_images', 'public');
        }

        $user = User::create([
            'name' => $pendingRegistration['name'],
            'email' => $pendingRegistration['email'],
            'password' => $pendingRegistration['password'],
            'user_dob' => $validated['user_dob'] ?? null,
            'user_phone' => $validated['user_phone'] ?? null,
            'user_bio' => $validated['user_bio'] ?? null,
            'user_image' => $profileImagePath,
            'is_verified' => true,
        ]);

        OtpVerifications::whereNull('user_id')
            ->where('email', $pendingRegistration['email'])
            ->where('is_verified', true)
            ->update(['user_id' => $user->id]);

        event(new Registered($user));

        $this->flashSuccessToast(
            title: 'Account ready',
            text: 'Registration confirmed. Please sign in to continue.',
            position: $this->toastPositionFor($user),
        );

        $this->clearPendingRegistration($request);

        return response()->json([
            'success' => true,
            'redirect' => route('login', absolute: false),
        ]);
    }

    /**
     * @return array{name: string, email: string, password: string}|null
     */
    private function pendingRegistration(Request $request): ?array
    {
        $pendingRegistration = $request->session()->get(self::PENDING_REGISTRATION_KEY);

        if (! is_array($pendingRegistration)
            || ! isset($pendingRegistration['name'], $pendingRegistration['email'], $pendingRegistration['password'])) {
            return null;
        }

        return $pendingRegistration;
    }

    private function clearPendingRegistration(Request $request): void
    {
        $request->session()->forget([
            self::PENDING_REGISTRATION_KEY,
            self::REGISTRATION_OTP_VERIFIED_KEY,
        ]);
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
            'timer' => 3000,
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
