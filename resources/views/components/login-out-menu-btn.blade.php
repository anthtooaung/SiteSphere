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
@php
    $trigger = $attributes->get('trigger', 'bottom');
    $buttonId = 'mobileDropdownDividerButton' . ucfirst($trigger);
    $dropdownId = 'mobileDropdownDivider' . ucfirst($trigger);
    $btnClass = $trigger === 'top' ? 'auth-menu-button' : 'mobile-nav-item';
    $placement = $trigger === 'top' ? 'bottom-end' : 'top';
@endphp
<button
    id="{{ $buttonId }}"
    data-dropdown-toggle="{{ $dropdownId }}"
    {{ $attributes->merge(['class' => $btnClass, 'data-dropdown-placement' => $placement]) }}
    type="button"
    style="font-family: var(--font-family); color: var(--text-color);"
>
    <x-far-user class="icon" />
    <span>Login</span>
</button>

<div id="{{ $dropdownId }}" class="auth-menu-dropdown hidden z-50" style="background-color: var(--background-color); color: var(--text-color); font-family: var(--font-family);">
    <ul class="auth-menu-list" aria-labelledby="{{ $buttonId }}">
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
