@extends('index')

@section('title')
    Forgot Password
@endsection

@push('styles')
    @vite(['resources/css/auth.css'])
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
                <section class="form-panel login-panel" aria-labelledby="forgot-password-title">
                    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-heading">
                            <p class="section-label">Password reset</p>
                            <h1 id="forgot-password-title">Forgot password?</h1>
                            <p>Enter your account email and we will send a 6-digit OTP.</p>
                        </div>

                        @if (session('status'))
                            <p class="auth-status-message">{{ session('status') }}</p>
                        @endif

                        <x-input-field id="reset-email" name="email" label="Email address" plain="true" type="email" placeholder="name@email.com" autocomplete="email" :value="old('email')" required autofocus>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9z"/>
                                <path d="M5 8l7 5 7-5" />
                            </svg>
                        </x-input-field>

                        <button type="submit" class="primary-button">Send OTP</button>

                        <a href="{{ route('login') }}" class="text-button">Back to login</a>
                    </form>
                </section>
            </div>
        </section>
    </main>
@endsection
