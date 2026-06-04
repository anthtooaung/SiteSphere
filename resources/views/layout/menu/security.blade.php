@extends('dashboard')

@section('title')
    Security
@endsection

@push('styles')
    @vite('resources/css/security.css')
@endpush

@section('content')
    @php
        $securityUser = ($securityUser ?? Auth::user())->loadMissing('settings');
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
        $settings = $securityUser->settings;
        $twoFactorEnabled = (bool) old('two_factor_enabled', $securityUser->two_factor_enabled);
        $postVisibilityEnabled = (bool) old('user_post_visible', $settings?->user_post_visible ?? false);
        $avatarUrl = $securityUser->getAvatarUrl();
        $initial = \Illuminate\Support\Str::of($securityUser->name)->trim()->substr(0, 1)->upper()->toString();
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} security-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content security-content" aria-labelledby="securityTitle">
            <section class="security-shell" data-security-page>
                <nav class="security-breadcrumbs" aria-label="Breadcrumb">
                    <x-fas-house class="security-breadcrumb-icon" aria-hidden="true" />
                    <span class="separator">›</span>
                    <span>Settings</span>
                    <span class="separator">›</span>
                    <span class="active">Security</span>
                </nav>

                <header class="security-header">
                    <h1 id="securityTitle">
                        <x-fas-shield-halved class="security-heading-icon" aria-hidden="true" />
                        <span>Security</span>
                    </h1>
                    <p>Manage account protection, posting visibility, and password changes.</p>
                </header>

                @if (session('success'))
                    <div class="security-message show success" role="status" aria-live="polite">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="security-message show error" role="alert">
                        <strong>Please check your security settings.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('security.update') }}" class="security-form" data-security-form>
                    @csrf
                    @method('PATCH')

                    <section class="settings-stack" aria-label="Security settings sections">
                        <article class="security-card">
                            <div class="security-card-header">
                                <div>
                                    <h2>Two-Factor Authentication (2FA)</h2>
                                    <p class="subtitle">
                                        When enabled, a verification code will be sent to your email whenever you sign in.
                                    </p>
                                </div>
                                <div class="toggle-area">
                                    <span class="toggle-label">{{ $twoFactorEnabled ? 'Enabled' : 'Disabled' }}</span>
                                    <label class="switch" aria-label="Toggle two-factor authentication">
                                        <input type="hidden" name="two_factor_enabled" value="0">
                                        <input type="checkbox" id="two-factor-toggle" name="two_factor_enabled" value="1"
                                            @checked($twoFactorEnabled) data-security-two-factor>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </article>

                        <article class="security-card">
                            <div class="security-card-header">
                                <div>
                                    <h2 class="visibility-heading">
                                        <span>Change Visibility</span>
                                        <span class="help-tooltip" tabindex="0" aria-label="Visibility help">
                                            <x-fas-circle-info class="help-icon" aria-hidden="true" />
                                            <span class="tooltip-text" role="tooltip">
                                                <span class="visibility-preview">
                                                    <span class="visibility-preview-title">Poster examples</span>
                                                    <span class="visibility-preview-user">
                                                        @if ($avatarUrl)
                                                            <img class="visibility-preview-avatar" src="{{ $avatarUrl }}" alt="{{ $securityUser->name }}">
                                                        @else
                                                            <span class="visibility-preview-avatar visibility-preview-anonymous" aria-hidden="true">
                                                                {{ $initial }}
                                                            </span>
                                                        @endif
                                                        <span>
                                                            <span class="visibility-preview-name">{{ $securityUser->name }}</span>
                                                            <span class="visibility-preview-label">Non-anonymous post</span>
                                                        </span>
                                                    </span>
                                                    <span class="visibility-preview-user">
                                                        <span class="visibility-preview-avatar visibility-preview-anonymous" aria-hidden="true">?</span>
                                                        <span>
                                                            <span class="visibility-preview-name">Anonymous</span>
                                                            <span class="visibility-preview-label">Anonymous post</span>
                                                        </span>
                                                    </span>
                                                </span>
                                            </span>
                                        </span>
                                    </h2>
                                    <p class="subtitle">Choose whether your posts can show your profile identity.</p>
                                </div>
                                <div class="toggle-area">
                                    <span class="toggle-label">{{ $postVisibilityEnabled ? 'Visible' : 'Anonymous' }}</span>
                                    <label class="switch" aria-label="Toggle posting visibility">
                                        <input type="hidden" name="user_post_visible" value="0">
                                        <input type="checkbox" id="anonymous-posting-toggle" name="user_post_visible" value="1"
                                            @checked($postVisibilityEnabled) data-security-visibility>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </article>

                        <article class="security-card" x-data="{ current: false, password: false, confirm: false }">
                            <h2>Change Password</h2>
                            <p class="subtitle">Leave password fields empty if you only want to save toggle changes.</p>

                            <div class="password-form">
                                <div class="field-group">
                                    <label for="current-password">Current Password</label>
                                    <div class="password-input-wrapper">
                                        <input :type="current ? 'text' : 'password'" id="current-password" name="current_password"
                                            placeholder="Enter current password" autocomplete="current-password"
                                            @class(['is-invalid' => $errors->has('current_password')])>
                                        <button class="visibility-btn" type="button" aria-label="Toggle current password visibility"
                                            :aria-pressed="current.toString()" @click="current = ! current">
                                            <x-fas-eye x-show="! current" aria-hidden="true" />
                                            <x-fas-eye-slash x-show="current" x-cloak aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label for="new-password">New Password</label>
                                    <div class="password-input-wrapper">
                                        <input :type="password ? 'text' : 'password'" id="new-password" name="password"
                                            placeholder="Enter new password" autocomplete="new-password"
                                            @class(['is-invalid' => $errors->has('password')])>
                                        <button class="visibility-btn" type="button" aria-label="Toggle new password visibility"
                                            :aria-pressed="password.toString()" @click="password = ! password">
                                            <x-fas-eye x-show="! password" aria-hidden="true" />
                                            <x-fas-eye-slash x-show="password" x-cloak aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label for="confirm-password">Confirm New Password</label>
                                    <div class="password-input-wrapper">
                                        <input :type="confirm ? 'text' : 'password'" id="confirm-password" name="password_confirmation"
                                            placeholder="Confirm new password" autocomplete="new-password">
                                        <button class="visibility-btn" type="button" aria-label="Toggle confirm password visibility"
                                            :aria-pressed="confirm.toString()" @click="confirm = ! confirm">
                                            <x-fas-eye x-show="! confirm" aria-hidden="true" />
                                            <x-fas-eye-slash x-show="confirm" x-cloak aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </section>

                    <footer class="footer-actions">
                        <button class="save-btn" type="submit" data-security-save>
                            <x-fas-floppy-disk class="save-btn-icon" aria-hidden="true" />
                            <span>Save Changes</span>
                        </button>
                    </footer>
                </form>
            </section>
        </main>
    </div>
@endsection
