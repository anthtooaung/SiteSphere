@props([
    'menuBarLocation' => 'left',
])

@php
    $user = Auth::user();
    $isAdmin = $user?->role === 'admin';
    $menuBarLocation = in_array($menuBarLocation, ['top', 'right', 'bottom', 'left'], true) ? $menuBarLocation : 'left';
    $isHorizontalMenu = in_array($menuBarLocation, ['top', 'bottom'], true);
    $isDashboardRoute = request()->routeIs('dashboard');

    $profileMenuItems = [
        [
            'label' => 'View Profile',
            'href' => route('profile-detail'),
            'icon' => 'user',
            'active' => request()->routeIs('profile-detail'),
        ],
        [
            'label' => 'Saved Post',
            'href' => route('saved-post'),
            'icon' => 'saved',
            'active' => request()->routeIs('saved-post'),
        ],
    ];

    $adminMenuItems = $isAdmin
        ? [
            [
                'label' => 'Dashboard',
                'href' => route('dashboard'),
                'icon' => 'dashboard',
                'active' => request()->routeIs('dashboard'),
            ],
            ['label' => 'Users', 'href' => route('users'), 'icon' => 'users', 'active' => request()->routeIs('users')],
            [
                'label' => 'Reports',
                'href' => route('reports'),
                'icon' => 'reports',
                'active' => request()->routeIs('reports'),
            ],
        ]
        : [];

    $settingMenuItems = [
        [
            'label' => 'Edit Profile',
            'href' => route('edit-profile'),
            'icon' => 'edit',
            'active' => request()->routeIs('edit-profile'),
        ],
        [
            'label' => 'Appearance',
            'href' => route('appearance'),
            'icon' => 'appearance',
            'active' => request()->routeIs('appearance'),
        ],
        [
            'label' => 'Edit Tag',
            'href' => route('edit-tag'),
            'icon' => 'tag',
            'active' => request()->routeIs('edit-tag'),
        ],
        [
            'label' => 'Security',
            'href' => route('security'),
            'icon' => 'security',
            'active' => request()->routeIs('security'),
        ],
    ];
@endphp

@auth
    <aside id="layoutMenu" data-menu-bar-location="{{ $menuBarLocation }}"
        {{ $attributes->class(['layout-menu', 'layout-menu--' . $menuBarLocation, 'layout-menu--horizontal' => $isHorizontalMenu, 'layout-menu--topbar' => $menuBarLocation === 'top']) }}>
        @if ($menuBarLocation === 'top')
            <nav class="layout-menu-topbar-nav" aria-label="Account top menu">
                <ul class="layout-menu-topbar-list">
                    @foreach ($profileMenuItems as $menuItem)
                        @php($isActive = $menuItem['active'] ?? false)
                        <li class="layout-menu-topbar-item">
                            <a href="{{ $menuItem['href'] }}" @class(['layout-menu-topbar-link', 'active' => $isActive])
                                @if ($isActive) aria-current="page" @endif>
                                @switch($menuItem['icon'])
                                    @case('dashboard')
                                        <x-fas-chart-pie class="icon" aria-hidden="true" />
                                    @break

                                    @case('saved')
                                        <x-fas-bookmark class="icon" aria-hidden="true" />
                                    @break

                                    @case('users')
                                        <x-fas-users class="icon" aria-hidden="true" />
                                    @break

                                    @case('reports')
                                        <x-fas-circle-info class="icon" aria-hidden="true" />
                                    @break

                                    @default
                                        <x-fas-user class="icon" aria-hidden="true" />
                                @endswitch
                                <span>{{ $menuItem['label'] }}</span>
                            </a>
                        </li>
                    @endforeach

                    @if ($isAdmin)
                        <li class="layout-menu-topbar-divider" aria-hidden="true"></li>

                        @foreach ($adminMenuItems as $menuItem)
                            @php($isActive = $menuItem['active'] ?? false)
                            <li class="layout-menu-topbar-item">
                                <a href="{{ $menuItem['href'] }}" @class(['layout-menu-topbar-link', 'active' => $isActive])
                                    @if ($isActive) aria-current="page" @endif>
                                    @switch($menuItem['icon'])
                                        @case('dashboard')
                                            <x-fas-chart-pie class="icon" aria-hidden="true" />
                                        @break

                                        @case('users')
                                            <x-fas-users class="icon" aria-hidden="true" />
                                        @break

                                        @case('reports')
                                            <x-fas-file-lines class="icon" aria-hidden="true" />
                                        @break
                                    @endswitch
                                    <span>{{ $menuItem['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    @endif

                    <li class="layout-menu-topbar-divider" aria-hidden="true"></li>

                    <li class="layout-menu-topbar-item layout-menu-topbar-settings" x-data="{ open: false }"
                        @click.outside="open = false">
                        <button type="button" class="layout-menu-topbar-link" id="layoutMenuSettingToggle"
                            aria-haspopup="true" :aria-expanded="open.toString()" @click="open = ! open">
                            <x-fas-cog class="icon" aria-hidden="true" />
                            <span>Setting</span>
                            <x-fas-chevron-down class="layout-menu-topbar-chevron" aria-hidden="true" />
                        </button>

                        <ul class="layout-menu-topbar-dropdown" id="layoutMenuSettingDropdown"
                            aria-labelledby="layoutMenuSettingToggle" x-show="open" x-cloak>
                            @foreach ($settingMenuItems as $menuItem)
                                <li>
                                    @php($isActive = $menuItem['active'] ?? false)
                                    <a href="{{ $menuItem['href'] }}" @class(['layout-menu-topbar-dropdown-link', 'active' => $isActive])
                                        @if ($isActive) aria-current="page" @endif>
                                        @switch($menuItem['icon'])
                                            @case('edit')
                                                <x-fas-user-pen class="icon" aria-hidden="true" />
                                            @break

                                            @case('security')
                                                <x-fas-user-shield class="icon" aria-hidden="true" />
                                            @break

                                            @case('tag')
                                                <x-fas-tags class="icon" aria-hidden="true" />
                                            @break

                                            @default
                                                <x-fas-palette class="icon" aria-hidden="true" />
                                        @endswitch
                                        <span>{{ $menuItem['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>

                    <li class="layout-menu-topbar-divider" aria-hidden="true"></li>

                    <li class="layout-menu-topbar-item">
                        <form method="POST" action="{{ route('logout') }}" class="layout-menu-topbar-logout">
                            @csrf
                            <button type="submit" class="layout-menu-topbar-link">
                                <x-fas-right-to-bracket class="icon" aria-hidden="true" />
                                <span>Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        @else
            <nav class="layout-menu-nav" aria-label="Account menu">
                <ul class="layout-menu-list">
                    @foreach ($profileMenuItems as $menuItem)
                        @php($isActive = $menuItem['active'] ?? false)
                        <li>
                            <a href="{{ $menuItem['href'] }}" @class(['layout-menu-link', 'active' => $isActive])
                                @if ($isActive) aria-current="page" @endif>
                                @switch($menuItem['icon'])
                                    @case('dashboard')
                                        <x-fas-chart-pie class="icon" aria-hidden="true" />
                                    @break

                                    @case('saved')
                                        <x-fas-bookmark class="icon" aria-hidden="true" />
                                    @break

                                    @case('users')
                                        <x-fas-users class="icon" aria-hidden="true" />
                                    @break

                                    @case('reports')
                                        <x-fas-file-lines class="icon" aria-hidden="true" />
                                    @break

                                    @default
                                        <x-fas-user class="icon" aria-hidden="true" />
                                @endswitch
                                <span>{{ $menuItem['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($isAdmin)
                    <div class="layout-menu-section">
                        <ul class="layout-menu-list">
                            @foreach ($adminMenuItems as $menuItem)
                                @php($isActive = $menuItem['active'] ?? false)
                                <li>
                                    <a href="{{ $menuItem['href'] }}" @class(['layout-menu-link', 'active' => $isActive])
                                        @if ($isActive) aria-current="page" @endif>
                                        @switch($menuItem['icon'])
                                            @case('dashboard')
                                                <x-fas-chart-pie class="icon" aria-hidden="true" />
                                            @break

                                            @case('users')
                                                <x-fas-users class="icon" aria-hidden="true" />
                                            @break

                                            @case('reports')
                                                <x-fas-file-lines class="icon" aria-hidden="true" />
                                            @break
                                        @endswitch
                                        <span>{{ $menuItem['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="layout-menu-section">
                    <p class="layout-menu-heading">Setting</p>
                    <ul class="layout-menu-list">
                        @foreach ($settingMenuItems as $menuItem)
                            <li>
                                @php($isActive = $menuItem['active'] ?? false)
                                <a href="{{ $menuItem['href'] }}" @class(['layout-menu-link', 'active' => $isActive])
                                    @if ($isActive) aria-current="page" @endif>
                                    @switch($menuItem['icon'])
                                        @case('security')
                                            <x-fas-user-shield class="icon" aria-hidden="true" />
                                        @break

                                        @case('edit')
                                            <x-fas-user-pen class="icon" aria-hidden="true" />
                                        @break

                                        @case('tag')
                                            <x-fas-tags class="icon" aria-hidden="true" />
                                        @break

                                        @default
                                            <x-fas-palette class="icon" aria-hidden="true" />
                                    @endswitch
                                    <span>{{ $menuItem['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </nav>

            <form method="POST" action="{{ route('logout') }}" class="layout-menu-logout ">
                @csrf
                <button type="submit" class="layout-menu-link layout-menu-action">
                    <x-fas-right-to-bracket class="icon" aria-hidden="true" />
                    <span>Logout</span>
                </button>
            </form>
        @endif
    </aside>
@endauth
