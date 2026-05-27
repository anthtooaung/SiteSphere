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
