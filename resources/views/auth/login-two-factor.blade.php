@extends('index')

@section('title')
    Login Verification
@endsection

@push('styles')
    @vite(['resources/css/auth.css'])
@endpush

@push('scripts')
    @vite(['resources/js/reset-password.js'])
@endpush

@section('content')
    <main class="auth-page">
        <section class="auth-shell password-reset-shell" aria-label="SiteSphere login verification">
            <a href="{{ route('login') }}" class="brand-title" aria-label="SiteSphere">
                <x-app-logo />
                <span class="brand-word" aria-hidden="true">
                    <span class="brand-sphere">SiteSphere</span>
                </span>
            </a>

            <div class="form-stage">
                <section class="form-panel login-panel" aria-labelledby="login-two-factor-title">
                    <section class="flow-step reset-otp-step is-step-entering" data-step="otp">
                        <div class="form-heading">
                            <p class="section-label">Two-factor authentication</p>
                            <h1 id="login-two-factor-title">Enter your OTP</h1>
                            <p>
                                Use the newest 6-digit code sent to
                                <strong>{{ $email }}</strong>.
                            </p>
                        </div>

                        @if (session('status'))
                            <p class="auth-status-message">{{ session('status') }}</p>
                        @endif

                        <form id="resetOtpForm" class="flow-form" method="POST" action="{{ route('login.two-factor.store') }}">
                            @csrf

                            <div class="field-group">
                                <label for="reset-otp-code-1">OTP code</label>
                                <div class="otp-code-row" role="group" aria-label="Enter 6 digit OTP code">
                                    <input id="reset-otp-code-1" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" required autofocus />
                                    <input id="reset-otp-code-2" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                    <input id="reset-otp-code-3" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                    <input id="reset-otp-code-4" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                    <input id="reset-otp-code-5" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                    <input id="reset-otp-code-6" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                </div>
                                <input id="reset-otp-code" type="hidden" name="otp_code" value="{{ old('otp_code') }}" />
                                @error('otp_code')
                                    <p class="field-error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="primary-button">Verify OTP</button>
                        </form>

                        <form method="POST" action="{{ route('login.two-factor.resend') }}" class="flow-form">
                            @csrf
                            <button type="submit" class="text-button">Resend OTP</button>
                            <a href="{{ route('login') }}" class="text-button">Back to login</a>
                        </form>
                    </section>
                </section>
            </div>
        </section>
    </main>
@endsection
