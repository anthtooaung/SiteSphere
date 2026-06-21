@extends('index')

@section('title')
    New Password
@endsection

@push('styles')
    @vite(['resources/css/auth.css'])
@endpush

@push('scripts')
    @vite(['resources/js/reset-password.js'])
@endpush

@section('content')
    <main class="auth-page">
        <section class="auth-shell password-reset-shell" aria-label="SiteSphere password reset">
            <a href="{{ route('login') }}" class="brand-title" aria-label="SiteSphere">
                <x-app-logo />
                <span class="brand-word" aria-hidden="true">
                    <span class="brand-sphere">SiteSphere</span>
                </span>
            </a>

            <div class="form-stage">
                <section class="form-panel login-panel" aria-labelledby="new-password-title">
                    <form class="auth-form" method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <div class="form-heading">
                            <p class="section-label">Password reset</p>
                            <h1 id="new-password-title">Create new password</h1>
                            <p>
                                OTP verified for
                                <strong>{{ $email }}</strong>.
                            </p>
                        </div>

                        <x-input-field id="reset-password" name="password" label="New password" plain="true" type="password" placeholder="New password" autocomplete="new-password" minlength="8" required pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}" title="Password must be at least 8 characters long and contain at least one letter, one number, and one special character." autofocus>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8a5 5 0 0 1 10 0v2" />
                                <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10z" />
                            </svg>
                            <x-slot:suffix>
                                <button type="button" class="password-toggle" id="toggleResetPassword" aria-label="Show password" aria-pressed="false">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" />
                                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                    </svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 3l18 18" />
                                        <path d="M10.6 10.6A2 2 0 0 0 12 14a2 2 0 0 0 1.4-.6" />
                                        <path d="M9.8 5.3A10 10 0 0 1 12 5c6 0 9.5 7 9.5 7a15.8 15.8 0 0 1-2.6 3.4" />
                                        <path d="M6.6 6.7C3.9 8.5 2.5 12 2.5 12s3.5 7 9.5 7a9.6 9.6 0 0 0 4.2-.9" />
                                    </svg>
                                </button>
                            </x-slot:suffix>
                        </x-input-field>

                        <x-input-field id="reset-password-confirmation" name="password_confirmation" label="Confirm new password" plain="true" type="password" placeholder="Confirm new password" autocomplete="new-password" required>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8a5 5 0 0 1 10 0v2" />
                                <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10z" />
                            </svg>
                            <x-slot:suffix>
                                <button type="button" class="password-toggle" id="toggleResetPasswordConfirmation" aria-label="Show password" aria-pressed="false">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" />
                                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                    </svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 3l18 18" />
                                        <path d="M10.6 10.6A2 2 0 0 0 12 14a2 2 0 0 0 1.4-.6" />
                                        <path d="M9.8 5.3A10 10 0 0 1 12 5c6 0 9.5 7 9.5 7a15.8 15.8 0 0 1-2.6 3.4" />
                                        <path d="M6.6 6.7C3.9 8.5 2.5 12 2.5 12s3.5 7 9.5 7a9.6 9.6 0 0 0 4.2-.9" />
                                    </svg>
                                </button>
                            </x-slot:suffix>
                        </x-input-field>

                        <button type="submit" class="primary-button">Reset password</button>
                    </form>
                </section>
            </div>
        </section>
    </main>
@endsection
