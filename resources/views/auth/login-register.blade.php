@extends('index')

@section('title')
    {{ $isLogin ? 'Login' : 'Register' }}
@endsection

@push('styles')
    @vite(['resources/css/auth.css'])
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const authToastPosition = {{ Illuminate\Support\Js::from($toastPosition ?? 'top-end') }};

        window.authToast = (options = {}) => {
            if (!window.Swal?.mixin) {
                return null;
            }

            const toast = window.Swal.mixin({
                toast: true,
                position: authToastPosition,
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                width: 'auto',
                height: '100px',
                didOpen: (toastElement) => {
                    toastElement.onmouseenter = window.Swal.stopTimer;
                    toastElement.onmouseleave = window.Swal.resumeTimer;
                },
                
            });

            return toast.fire({
                ...options,
                showConfirmButton: false,
            });
        };
    </script>
    @vite(['resources/js/auth.js'])
@endpush

@section('content')
    <main class="auth-page">
        <section class="auth-shell {{ !$isLogin ? 'is-register' : '' }}" id="authShell" aria-label="SiteSphere authentication">
{{--            Logo time--}}
            <div class=" flex absolute top-0 translate-x-5 mt-5 gap-2 z-30">
                <x-app-logo></x-app-logo>
                <span class="self-center text-xl text-heading font-bold whitespace-nowrap text-[var(--accent-color)]" >SiteSphere</span>
           </div>
            <div class="form-stage">

                <!-- Login Panel -->
                <section class="form-panel login-panel" aria-labelledby="login-title">
                    <form id="loginForm" class="auth-form" method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <div class="form-heading">
                            <p class="section-label">Welcome back</p>
                            <h1 id="login-title">Login</h1>
                            <p>Sign in to continue your journey.</p>
                        </div>

                        <!-- Social buttons row -->
                        <div class="social-row">
                            <a href="{{ route('social.redirect', 'google') }}" class="social-button" id="googleConnectBtn" aria-label="Continue with Google">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18A10.96 10.96 0 0 0 1 12c0 1.77.42 3.44 1.18 4.94l3.66-2.84z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06L5.84 9.9C6.71 7.31 9.14 5.38 12 5.38z"/>
                                </svg>
                            </a>
                            <a href="{{ route('social.redirect', 'github') }}" class="social-button" id="githubConnectBtn" aria-label="Continue with GitHub">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.58.11.79-.25.79-.56v-2.15c-3.2.7-3.87-1.36-3.87-1.36-.52-1.33-1.28-1.68-1.28-1.68-1.04-.71.08-.7.08-.7 1.15.08 1.76 1.18 1.76 1.18 1.03 1.75 2.69 1.24 3.35.95.1-.74.4-1.24.73-1.53-2.55-.29-5.23-1.28-5.23-5.69 0-1.26.45-2.29 1.18-3.09-.12-.29-.51-1.46.11-3.05 0 0 .96-.31 3.15 1.18A10.8 10.8 0 0 1 12 6.02c.98 0 1.96.13 2.88.39 2.18-1.49 3.14-1.18 3.14-1.18.63 1.59.24 2.76.12 3.05.74.8 1.18 1.83 1.18 3.09 0 4.42-2.69 5.39-5.25 5.67.41.36.78 1.06.78 2.14v3.17c0 .31.21.68.8.56A11.51 11.51 0 0 0 23.5 12C23.5 5.65 18.35.5 12 .5z"/>
                                </svg>
                            </a>
                        </div>

                        <div class="divider"><span>or use your email</span></div>

                        <!-- Email input field -->
                        <x-input-field id="login-email" name="email" label="Email address" plain="true" type="email" placeholder="name@email.com" autocomplete="email" :value="old('email')" bag="login" required>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9z"/>
                                <path d="M5 8l7 5 7-5" />
                            </svg>
                        </x-input-field>

                        <!-- Password input field -->
                        <x-input-field id="login-password" name="password" plain="true" type="password" placeholder="Enter your password" autocomplete="current-password" bag="login" required>
                            <x-slot:labelRow>
                                <div class="label-row">
                                    <label for="login-password">Password</label>
                                    <button type="button" class="text-button" id="forgotPasswordBtn">
                                        Forgot password?
                                    </button>
                                </div>
                            </x-slot:labelRow>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8a5 5 0 0 1 10 0v2" />
                                <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10z" />
                            </svg>
                            <x-slot:suffix>
                                <button type="button" class="password-toggle" id="toggleLoginPassword" aria-label="Show password" aria-pressed="false">
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

                        <button type="submit" class="primary-button">Login</button>
                    </form>
                </section>

                <!-- Register Panel -->
                <section class="form-panel register-panel" aria-labelledby="register-title">
                    <form id="registerForm" class="auth-form" method="POST" action="{{ route('register.initiate') }}" novalidate>
                        @csrf
                        <div class="form-heading">
                            <p class="section-label">Join SiteSphere</p>
                            <h1 id="register-title">Registration</h1>
                            <p>Create an account using your details below.</p>
                        </div>

                        <!-- Username input field -->
                        <x-input-field id="reg-name" name="name" label="Username" plain="true" type="text" placeholder="Username" autocomplete="username" required>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" />
                                <path d="M4 20a8 8 0 0 1 16 0" />
                            </svg>
                        </x-input-field>

                        <!-- Email input field -->
                        <x-input-field id="reg-email" name="email" label="Email address" plain="true" type="email" placeholder="name@email.com" autocomplete="email" required>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9z"/>
                                <path d="M5 8l7 5 7-5" />
                            </svg>
                        </x-input-field>

                        <!-- Password input field -->
                        <x-input-field id="reg-password" name="password" label="Password" plain="true" type="password" placeholder="Create password" autocomplete="new-password" minlength="8" aria-describedby="passwordHint" title="Use at least 8 characters and at least 3 special characters." required>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8a5 5 0 0 1 10 0v2" />
                                <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10z" />
                            </svg>
                            <x-slot:suffix>
                                <button type="button" class="password-toggle" id="toggleRegisterPassword" aria-label="Show password" aria-pressed="false">
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

                        <!-- Confirm Password input field -->
                        <x-input-field id="reg-confirm" name="password_confirmation" label="Confirm password" plain="true" type="password" placeholder="Confirm password" autocomplete="new-password" minlength="8" required>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8a5 5 0 0 1 10 0v2" />
                                <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10z" />
                            </svg>
                            <x-slot:suffix>
                                <button type="button" class="password-toggle" id="toggleRegisterConfirmPassword" aria-label="Show password" aria-pressed="false">
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

                        <button type="submit" class="primary-button">Register</button>
                    </form>
                </section>
            </div>

            <!-- Slider Panel -->
            <aside class="slider-panel" aria-live="polite">
                <div class="slider-content slider-login">
                    <p class="slider-kicker">WELCOME TO SITESPHERE</p>
                    <h2>Discover Better Resources</h2>
                    <p>Create your account and explore the best tools, websites, and technologies for your projects.</p>
                    <button type="button" class="ghost-button" id="showRegister">
                        Create Account
                    </button>
                </div>
                <div class="slider-content slider-register">
                    <p class="slider-kicker">WELCOME BACK</p>
                    <h2>Already have an account?</h2>
                    <p>Sign in to continue exploring tools, resources, and projects on SiteSphere.</p>
                    <ol class="register-timeline" aria-label="Registration timeline">
                        <li data-guide="account">
                            <span>1</span>
                            <div>
                                <strong>Account details</strong>
                                <small>Username, email, and secure password.</small>
                            </div>
                        </li>
                        <li data-guide="otp">
                            <span>2</span>
                            <div>
                                <strong>Email OTP</strong>
                                <small>Verify the newest 5-minute code.</small>
                            </div>
                        </li>
                        <li data-guide="profile">
                            <span>3</span>
                            <div>
                                <strong>Profile info</strong>
                                <small>Add optional personal details.</small>
                            </div>
                        </li>
                        <li data-guide="confirm">
                            <span>4</span>
                            <div>
                                <strong>Confirm</strong>
                                <small>Preview everything, then return to login.</small>
                            </div>
                        </li>
                    </ol>
                    <button type="button" class="ghost-button" id="showLogin">
                        Login
                    </button>
                </div>
            </aside>
        </section>

        <!-- Registration Steps Modal -->
        <div class="registration-modal is-hidden" id="registrationModal" role="dialog" aria-modal="true" aria-labelledby="flowTitle">
            <div class="registration-card">
                <button type="button" class="modal-close" id="closeRegistrationFlow" aria-label="Close registration flow">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 6l12 12" />
                        <path d="M18 6L6 18" />
                    </svg>
                </button>

                <!-- OTP Step -->
                <section class="flow-step" data-step="otp">
                    <div class="form-heading compact-heading">
                        <h2 id="flowTitle">Verify your OTP</h2>
                        <p>
                            We sent a 6-digit OTP to
                            <strong id="otpEmailTarget">your email</strong>.
                        </p>
                    </div>

                    <form id="otpForm" class="flow-form">
                        <div class="field-group">
                            <label for="otp-code-1">OTP code</label>
                            <div class="otp-code-row" role="group" aria-label="Enter 6 digit OTP code">
                                <input id="otp-code-1" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" required />
                                <input id="otp-code-2" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                <input id="otp-code-3" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                <input id="otp-code-4" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                <input id="otp-code-5" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                                <input id="otp-code-6" class="plain-input otp-digit" type="text" inputmode="numeric" maxlength="1" required />
                            </div>
                            <input id="otp-code" type="hidden" name="otp_code" />
                        </div>

                        <div class="otp-meta">
                            <span>Expires in <strong id="otpTimer">05:00</strong></span>
                            <button type="button" class="text-button" id="resendOtpBtn">
                                Resend OTP
                            </button>
                        </div>
                        <button type="submit" class="primary-button">Verify OTP</button>
                    </form>
                </section>

                <!-- Profile Step -->
                <section class="flow-step is-hidden" data-step="profile">
                    <div class="form-heading compact-heading">
                        <p class="section-label">Optional profile</p>
                        <h2>Profile Information</h2>
                        <p>You can add these now or leave them blank.</p>
                    </div>

                    <form id="profileForm" class="flow-form" enctype="multipart/form-data">
                        <div class="field-group">
                            <label for="profile-dob">Date of birth</label>
                            <input id="profile-dob" name="user_dob" class="plain-input" type="date" />
                        </div>

                        <div class="field-group">
                            <label for="profile-phone">Phone number</label>
                            <input id="profile-phone" name="user_phone" class="plain-input" type="tel" placeholder="+95 9..." />
                        </div>

                        <div class="field-group">
                            <label for="profile-bio">Bio</label>
                            <textarea id="profile-bio" name="user_bio" class="plain-input textarea-input" placeholder="Short profile bio" rows="3"></textarea>
                        </div>

                        <div class="field-group">
                            <label for="profile-image">Profile image</label>
                            <input id="profile-image" name="user_image" class="plain-input file-input" type="file" accept="image/*" />
                            <div class="image-preview is-hidden" id="profileImagePreview">
                                <img id="profileImageThumb" alt="Profile preview" />
                            </div>
                        </div>

                        <div class="flow-actions">
                            <button type="button" class="secondary-button" id="skipProfileBtn">
                                Skip
                            </button>
                            <button type="submit" class="primary-button" id="continueProfileBtn">
                                Continue
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Confirm Step -->
                <section class="flow-step is-hidden" data-step="confirm">
                    <div class="form-heading compact-heading">
                        <p class="section-label">Final check</p>
                        <h2>Confirm details</h2>
                        <p>Review the information before creating the account.</p>
                    </div>

                    <div class="layout-preview">
                        <div class="preview-avatar" id="confirmAvatar">S</div>
                        <div>
                            <strong id="confirmUsername">Username</strong>
                            <span id="confirmLayoutText">SiteSphere member profile</span>
                        </div>
                    </div>

                    <div class="confirm-preview" id="confirmPreview"></div>

                    <div class="flow-actions">
                        <button type="button" class="secondary-button" id="backToProfileBtn">
                            Back
                        </button>
                        <button type="button" class="primary-button" id="confirmRegisterBtn">
                            Confirm account
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </main>
@endsection
