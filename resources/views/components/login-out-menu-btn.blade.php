@desktop
<button
    id="dropdownDividerButton"
    data-dropdown-toggle="dropdownDivider"
    class="auth-menu-button"
    type="button"
>
    <x-far-user class="icon" />
    <span>Login / Register</span>
    <x-fas-chevron-down class="auth-menu-chevron" aria-hidden="true" />
</button>

<div id="dropdownDivider" class="auth-menu-dropdown hidden">
    <ul class="auth-menu-list" aria-labelledby="dropdownDividerButton">
        <li>
            <a href="{{ route('login') }}" class="auth-menu-link">
                Login
            </a>
        </li>
    </ul>
    <div class="auth-menu-list">
        <a href="{{ route('register') }}" class="auth-menu-link">
            Register
        </a>
    </div>
</div>
@enddesktop

@mobile
<button
    id="mobileDropdownDividerButton"
    data-dropdown-toggle="mobileDropdownDivider"
    {{ $attributes->merge(['class' => 'mobile-nav-item', 'data-dropdown-placement' => 'top']) }}
    type="button"
    style="font-family: var(--font-family); color: var(--text-color);"
>
    <x-far-user class="icon" />
    <span>Login</span>
</button>

<div id="mobileDropdownDivider" class="auth-menu-dropdown hidden z-50" style="background-color: var(--background-color); color: var(--text-color); font-family: var(--font-family);">
    <ul class="auth-menu-list" aria-labelledby="mobileDropdownDividerButton">
        <li>
            <a href="{{ route('login') }}" class="auth-menu-link">
                Login
            </a>
        </li>
    </ul>
    <div class="auth-menu-list">
        <a href="{{ route('register') }}" class="auth-menu-link">
            Register
        </a>
    </div>
</div>
@endmobile
