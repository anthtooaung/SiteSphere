@php
    $user = Auth::user();
    $isAdmin = $user?->role === 'admin';

    $primaryMenuItems = [
        ['label' => 'View Profile', 'href' => '#'],
    ];

    if ($isAdmin) {
        $primaryMenuItems = array_merge($primaryMenuItems, [
            ['label' => 'Dashboard', 'href' => route('dashboard')],
            ['label' => 'Users', 'href' => '#'],
            ['label' => 'Reports', 'href' => '#'],
        ]);
    }

    $primaryMenuItems[] = ['label' => 'Saved Post', 'href' => '#'];

    $settingMenuItems = [
        ['label' => 'Edit Profile', 'href' => '#'],
        ['label' => 'Appearance', 'href' => '#'],
        ['label' => 'Security', 'href' => '#'],
    ];
@endphp

@desktop
<div class="desktop-action">
    <button
        type="button"
        {{ $attributes->merge(['class' => 'account-button']) }}
        id="desktopAccountMenuButton"
        data-dropdown-toggle="desktopAccountMenu"
        data-dropdown-placement="bottom-end"
        aria-label="Account menu"
        aria-expanded="false"
    >
        @if($user->user_image)
            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="size-8 rounded-full object-cover" />
        @else
            <x-far-user class="icon" />
        @endif
        <span class="account-text">
            <span class="verified-label">
                Verified <x-fas-check-circle class="inline-block size-3" style="color: var(--accent-color);" />
            </span>
            <span class="account-name">{{ $user->name }}</span>
        </span>
    </button>

    <div id="desktopAccountMenu" class="account-menu-dropdown hidden" aria-labelledby="desktopAccountMenuButton">
        <ul class="account-menu-list">
            @foreach($primaryMenuItems as $menuItem)
                <li>
                    <a href="{{ $menuItem['href'] }}" class="account-menu-link">{{ $menuItem['label'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="account-menu-section">
            <p class="account-menu-heading">Setting</p>
            <ul class="account-menu-list">
                @foreach($settingMenuItems as $menuItem)
                    <li>
                        <a href="{{ $menuItem['href'] }}" class="account-menu-link">{{ $menuItem['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="account-menu-logout">
            @csrf
            <button type="submit" class="account-menu-link account-menu-action">Logout</button>
        </form>
    </div>
</div>
@enddesktop

@mobile
<div class="mobile-account-menu-wrap">
    <button
        type="button"
        {{ $attributes->merge(['class' => 'mobile-nav-item']) }}
        id="mobileAccountMenuButton"
        data-dropdown-toggle="mobileAccountMenu"
        data-dropdown-placement="top"
        aria-label="Account menu"
        aria-expanded="false"
    >
        @if($user->user_image)
            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="size-6 rounded-full object-cover" />
        @else
            <x-far-user class="icon"/>
        @endif
        <span>Profile</span>
    </button>

    <div id="mobileAccountMenu" class="account-menu-dropdown account-menu-dropdown--mobile hidden" aria-labelledby="mobileAccountMenuButton">
        <ul class="account-menu-list">
            @foreach($primaryMenuItems as $menuItem)
                <li>
                    <a href="{{ $menuItem['href'] }}" class="account-menu-link">{{ $menuItem['label'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="account-menu-section">
            <p class="account-menu-heading">Setting</p>
            <ul class="account-menu-list">
                @foreach($settingMenuItems as $menuItem)
                    <li>
                        <a href="{{ $menuItem['href'] }}" class="account-menu-link">{{ $menuItem['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="account-menu-logout">
            @csrf
            <button type="submit" class="account-menu-link account-menu-action">Logout</button>
        </form>
    </div>
</div>
@endmobile
