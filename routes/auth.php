<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\LoginTwoFactorChallengeController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::post('register/initiate', [RegisteredUserController::class, 'initiate'])
        ->middleware('throttle:5,1')
        ->name('register.initiate');

    Route::post('register/verify-otp', [RegisteredUserController::class, 'verifyOtp'])
        ->name('register.verify_otp');

    Route::post('register/resend-otp', [RegisteredUserController::class, 'resendOtp'])
        ->middleware('throttle:3,1')
        ->name('register.resend_otp');

    Route::post('register/finalize', [RegisteredUserController::class, 'finalize'])
        ->name('register.finalize');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('login/two-factor', [LoginTwoFactorChallengeController::class, 'create'])
        ->name('login.two-factor');

    Route::post('login/two-factor', [LoginTwoFactorChallengeController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.two-factor.store');

    Route::post('login/two-factor/resend', [LoginTwoFactorChallengeController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('login.two-factor.resend');

    Route::get('auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])
        ->whereIn('provider', ['google', 'github'])
        ->name('social.redirect');

    Route::get('auth/{provider}/callback', [SocialLoginController::class, 'callback'])
        ->whereIn('provider', ['google', 'github'])
        ->name('social.callback');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('reset-password-otp', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password-otp', [NewPasswordController::class, 'verifyOtp'])
        ->name('password.otp.verify');

    Route::get('reset-password-new', [NewPasswordController::class, 'edit'])
        ->name('password.new');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::match(['get', 'post'], 'logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
