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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SweetAlert2\Laravel\Swal;
use Throwable;

class RegisteredUserController extends Controller
{
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
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_verified' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Initiate registration and generate OTP.
     */
    public function initiate(Request $request): JsonResponse
    {
        // Custom email uniqueness check that ignores unverified users
        $user = User::where('email', $request->email)->first();
        if ($user && $user->is_verified) {
            $request->validate([
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        if ($user) {
            $user->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
            ]);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_verified' => false,
            ]);
        }

        // Generate 6 digit OTP code
        $otpCode = (string) rand(100000, 999999);

        // Store OTP record
        OtpVerifications::create([
            'user_id' => $user->id,
            'otp' => $otpCode,
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        // Log OTP code
        Log::info("OTP verification code for {$user->email}: {$otpCode}");

        $otpDeliveryFailed = false;
        $deliveryMessage = null;
        $mailPassword = (string) config('mail.mailers.smtp.password');

        if ($mailPassword === '' || Str::contains($mailPassword, 'replace-with-gmail-app-password')) {
            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_delivery_failed' => true,
                'message' => 'OTP created, but email is not configured. Set a real Gmail App Password in MAIL_PASSWORD and try Resend OTP.',
            ]);
        }

        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otpCode));
        } catch (Throwable $exception) {
            $otpDeliveryFailed = true;
            $deliveryMessage = $exception->getMessage();

            report($exception);
        }

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'email' => $user->email,
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
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->is_verified) {
            return response()->json([
                'message' => 'This account is already verified.',
            ], 400);
        }

        // Generate 6 digit OTP code
        $otpCode = (string) rand(100000, 999999);

        // Store OTP record
        OtpVerifications::create([
            'user_id' => $user->id,
            'otp' => $otpCode,
            'is_verified' => false,
            'expire_at' => now()->addMinutes(5),
        ]);

        // Log OTP code
        Log::info("Resent OTP verification code for {$user->email}: {$otpCode}");

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
            Mail::to($user->email)->send(new OtpVerificationMail($otpCode));
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
            'user_id' => ['required', 'exists:users,id'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $verification = OtpVerifications::where('user_id', $request->user_id)
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

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Finalize registration and save optional profile info.
     */
    public function finalize(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'user_dob' => ['nullable', 'date'],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'user_bio' => ['nullable', 'string', 'max:1000'],
            'user_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = User::findOrFail($request->user_id);

        $hasVerifiedOtp = OtpVerifications::where('user_id', $user->id)
            ->where('is_verified', true)
            ->exists();

        if (! $hasVerifiedOtp) {
            return response()->json([
                'message' => 'OTP verification must be completed first.',
            ], 403);
        }

        // Update profile fields
        $user->user_dob = $request->user_dob;
        $user->user_phone = $request->user_phone;
        $user->user_bio = $request->user_bio;

        if ($request->hasFile('user_image')) {
            $path = $request->file('user_image')->store('profile_images', 'public');
            $user->user_image = $path;
        }

        $user->is_verified = true;
        $user->save();

        event(new Registered($user));

        $this->flashSuccessToast(
            title: 'Account ready',
            text: 'Registration confirmed. Please sign in to continue.',
            position: $this->toastPositionFor($user),
        );

        return response()->json([
            'success' => true,
            'redirect' => route('login', absolute: false),
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
