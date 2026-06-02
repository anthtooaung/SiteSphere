@props([
    'menuBarLocation' => 'left',
])

@php
    $user = Auth::user();
    $isAdmin = $user?->role === 'admin';
    $menuBarLocation = in_array($menuBarLocation, ['top', 'right', 'bottom', 'left'], true)
        ? $menuBarLocation
        : 'left';

    $primaryMenuItems = [
        ['label' => 'View Profile', 'href' => '#', 'icon' => 'user'],
        ['label' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'dashboard'],
        ['label' => 'Saved Post', 'href' => '#', 'icon' => 'saved'],
    ];

    if ($isAdmin) {
        $primaryMenuItems = array_merge($primaryMenuItems, [
            ['label' => 'Users', 'href' => '#', 'icon' => 'users'],
            ['label' => 'Reports', 'href' => '#', 'icon' => 'reports'],
        ]);
    }

    $settingMenuItems = [
        ['label' => 'Appearance', 'href' => '#', 'icon' => 'appearance'],
        ['label' => 'Security', 'href' => '#', 'icon' => 'security'],
        ['label' => 'Edit Profile', 'href' => '#', 'icon' => 'edit'],
    ];
@endphp

@auth
    <aside
        id="layoutMenu"
        data-menu-bar-location="{{ $menuBarLocation }}"
        {{ $attributes->class(['layout-menu', 'layout-menu--'.$menuBarLocation]) }}
    >
        <div class="layout-menu-header">
            <div>
                <p class="layout-menu-kicker">SiteSphere</p>
                <h2>Menu</h2>
            </div>
        </div>

        <nav class="layout-menu-nav" aria-label="Dashboard menu">
            <ul class="layout-menu-list">
                @foreach ($primaryMenuItems as $menuItem)
                    <li>
                        <a href="{{ $menuItem['href'] }}" class="layout-menu-link">
                            @switch($menuItem['icon'])
                                @case('dashboard')
                                    <x-fas-gauge-high class="icon" aria-hidden="true" />
                                    @break

                                @case('saved')
                                    <x-fas-bookmark class="icon" aria-hidden="true" />
                                    @break

                                @case('users')
                                    <x-far-user class="icon" aria-hidden="true" />
                                    @break

                                @case('reports')
                                    <x-fas-circle-info class="icon" aria-hidden="true" />
                                    @break

                                @default
                                    <x-far-user class="icon" aria-hidden="true" />
                            @endswitch
                            <span>{{ $menuItem['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="layout-menu-section">
                <p class="layout-menu-heading">Setting</p>
                <ul class="layout-menu-list">
                    @foreach ($settingMenuItems as $menuItem)
                        <li>
                            <a href="{{ $menuItem['href'] }}" class="layout-menu-link">
                                @switch($menuItem['icon'])
                                    @case('security')
                                        <x-fas-check-circle class="icon" aria-hidden="true" />
                                        @break

                                    @case('edit')
                                        <x-fas-user-pen class="icon" aria-hidden="true" />
                                        @break

                                    @default
                                        <x-fas-layer-group class="icon" aria-hidden="true" />
                                @endswitch
                                <span>{{ $menuItem['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="layout-menu-logout">
            @csrf
            <button type="submit" class="layout-menu-link layout-menu-action">
                <x-fas-right-to-bracket class="icon" aria-hidden="true" />
                <span>Logout</span>
            </button>
        </form>
    </aside>
@endauth
