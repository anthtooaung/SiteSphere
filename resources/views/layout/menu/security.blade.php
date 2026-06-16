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
        $postVisibilityEnabled = (bool) old('user_post_visible', $settings?->user_post_visible ?? true);
        $avatarUrl = $securityUser->getAvatarUrl();
        $initial = \Illuminate\Support\Str::of($securityUser->name)->trim()->substr(0, 1)->upper()->toString();
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} security-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content security-content" aria-labelledby="securityTitle">
            <section class="security-shell" data-security-page x-data="securityPage({{ $twoFactorEnabled ? 'true' : 'false' }}, {{ $postVisibilityEnabled ? 'true' : 'false' }})">

                <header class="security-header">
                    <h1 id="securityTitle">
                        <x-fas-shield-halved class="security-heading-icon" aria-hidden="true" />
                        <span>Security</span>
                    </h1>
                    <p>Manage account protection, posting visibility, and password changes.</p>
                </header>



                <form method="POST" action="{{ route('security.update') }}" class="security-form" data-security-form @submit.prevent="submitForm($el)">
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
                                    <span class="toggle-label" x-text="twoFactor ? 'Enabled' : 'Disabled'">{{ $twoFactorEnabled ? 'Enabled' : 'Disabled' }}</span>
                                    <label class="switch" aria-label="Toggle two-factor authentication">
                                        <input type="hidden" name="two_factor_enabled" value="0">
                                        <input type="checkbox" id="two-factor-toggle" name="two_factor_enabled" value="1"
                                            x-model="twoFactor" data-security-two-factor>
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
                                    <span class="toggle-label" x-text="visibility ? 'Visible' : 'Anonymous'">{{ $postVisibilityEnabled ? 'Visible' : 'Anonymous' }}</span>
                                    <label class="switch" aria-label="Toggle posting visibility">
                                        <input type="hidden" name="user_post_visible" value="0">
                                        <input type="checkbox" id="anonymous-posting-toggle" name="user_post_visible" value="1"
                                            x-model="visibility" data-security-visibility>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </article>

                        @php
                            $isOauthOnly = $securityUser->isOauthOnly();
                        @endphp
                        <article class="security-card @if($isOauthOnly) is-disabled @endif" x-data="{ showForm: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }}, current: false, password: false, confirm: false }">
                            <div class="security-card-header">
                                <div>
                                    <h2>Change Password</h2>
                                    <p class="subtitle">
                                        @if ($isOauthOnly)
                                            Password management is handled by your linked social account.
                                        @else
                                            Update your account password to keep your account secure.
                                        @endif
                                    </p>
                                </div>
                                <div class="toggle-area">
                                    <button type="button" class="update-password-btn" 
                                        @if ($isOauthOnly) disabled title="Password is managed via OAuth" @endif
                                        @click="showForm = !showForm"
                                        x-text="showForm ? 'Cancel' : 'Update Password'"
                                        :aria-expanded="showForm.toString()"
                                        aria-controls="password-fields-container">
                                        Update Password
                                    </button>
                                </div>
                            </div>

                            <div id="password-fields-container" x-show="showForm" x-collapse x-cloak>
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
                            </div>
                        </article>
                    </section>

                    <footer class="footer-actions">
                        <button type="submit" class="save-btn" data-security-save :class="{ 'is-loading': isSubmitting }" :disabled="isSubmitting">
                            <span class="button-label">
                                <svg xmlns="http://www.w3.org/2000/svg" class="save-btn-icon" viewBox="0 0 16 16"
                                    fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M8.5 1.5A1.5 1.5 0 0 1 10 3v1.5A1.5 1.5 0 0 1 8.5 6h-3A1.5 1.5 0 0 1 4 4.5v-3h4.5Z" />
                                    <path
                                        d="M2 1.5A1.5 1.5 0 0 1 3.5 0h7.086a1.5 1.5 0 0 1 1.061.44l3.914 3.913A1.5 1.5 0 0 1 16 5.414V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5h-.5A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0H2v1.5Zm3 9A1 1 0 0 0 4 11.5V16h8v-4.5a1 1 0 0 0-1-1H5Z" />
                                </svg>
                                <span>Save Changes</span>
                            </span>
                            <span class="button-loader" aria-hidden="true">
                                <i></i><i></i><i></i>
                            </span>
                        </button>
                    </footer>
                </form>
            </section>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        function securityPage(initialTwoFactor = false, initialVisibility = false) {
            return {
                isSubmitting: false,
                twoFactor: initialTwoFactor,
                visibility: initialVisibility,
                async submitForm(formElement) {
                    if (this.isSubmitting) return;

                    const result = await window.sitesphereSwal.confirm({
                        title: 'Save Changes?',
                        text: 'Are you sure you want to apply these security settings?'
                    });

                    if (result.isConfirmed) {
                        this.isSubmitting = true;
                        formElement.submit();
                    }
                }
            };
        }
    </script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.sitesphereSwal.toast({
                    title: "{{ session('success') }}"
                });
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.sitesphereSwal.toast({
                    icon: 'error',
                    title: "{{ implode(' ', $errors->all()) }}"
                });
            });
        </script>
    @endif
@endpush

