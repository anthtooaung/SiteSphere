@extends('index')

@section('title')
    New Password
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

                        <x-input-field id="reset-password" name="password" label="New password" plain="true" type="password" placeholder="New password" autocomplete="new-password" required autofocus>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8a5 5 0 0 1 10 0v2" />
                                <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10z" />
                            </svg>
                        </x-input-field>

                        <x-input-field id="reset-password-confirmation" name="password_confirmation" label="Confirm new password" plain="true" type="password" placeholder="Confirm new password" autocomplete="new-password" required>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8a5 5 0 0 1 10 0v2" />
                                <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10z" />
                            </svg>
                        </x-input-field>

                        <button type="submit" class="primary-button">Reset password</button>
                    </form>
                </section>
            </div>
        </section>
    </main>
@endsection
