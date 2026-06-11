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
    class="auth-menu-button mobile-user-pill"
    type="button"
    style="border: 1px solid color-mix(in srgb, var(--background-color) 20%, transparent); background: color-mix(in srgb, var(--background-color) 12%, transparent); padding: 6px 12px; gap: 8px; border-radius: 999px;"
>
    <x-far-user class="icon size-4" />
    <span>Login</span>
</button>

<div id="mobileDropdownDivider" class="auth-menu-dropdown hidden z-50">
    <ul class="auth-menu-list" aria-labelledby="mobileDropdownDividerButton">
        <li>
            <a href="{{ route('login') }}" class="auth-menu-link block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                Login
            </a>
        </li>
    </ul>
    <div class="auth-menu-list">
        <a href="{{ route('register') }}" class="auth-menu-link block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
            Register
        </a>
    </div>
</div>
@endmobile
