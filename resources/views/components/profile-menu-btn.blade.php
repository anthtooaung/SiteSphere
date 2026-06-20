@php
    $user = Auth::user();
    $isAdmin = $user?->role === 'admin';
    $isDashboardRoute = request()->routeIs('dashboard');

    $profileMenuItems = [
        ['label' => 'View Profile', 'href' => route('profile-detail'), 'active' => request()->routeIs('profile-detail')],
        ['label' => 'Saved Post', 'href' => route('saved-post'), 'active' => request()->routeIs('saved-post')],
    ];

    $adminMenuItems = $isAdmin
        ? [
            ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
            ['label' => 'Users', 'href' => route('users'), 'active' => request()->routeIs('users')],
            ['label' => 'Reports', 'href' => route('reports'), 'active' => request()->routeIs('reports')],
        ]
        : [];

    $settingMenuItems = [
        ['label' => 'Edit Profile', 'href' => route('edit-profile'), 'active' => request()->routeIs('edit-profile')],
        ['label' => 'Appearance', 'href' => route('appearance'), 'active' => request()->routeIs('appearance')],
        ['label' => 'Edit Tag', 'href' => route('edit-tag'), 'active' => request()->routeIs('edit-tag')],
        ['label' => 'Security', 'href' => route('security'), 'active' => request()->routeIs('security')],
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
            @foreach($profileMenuItems as $menuItem)
                @php
                    $isActive = $menuItem['active'] ?? false;
                @endphp
                <li>
                    <a href="{{ $menuItem['href'] }}" @class(['account-menu-link', 'active' => $isActive]) @if($isActive) aria-current="page" @endif>
                        {{ $menuItem['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        @if($isAdmin)
            <div class="account-menu-section">
                <ul class="account-menu-list">
                    @foreach($adminMenuItems as $menuItem)
                        @php
                            $isActive = $menuItem['active'] ?? false;
                        @endphp
                        <li>
                            <a href="{{ $menuItem['href'] }}" @class(['account-menu-link', 'active' => $isActive]) @if($isActive) aria-current="page" @endif>
                                {{ $menuItem['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="account-menu-section">
            <p class="account-menu-heading">Setting</p>
            <ul class="account-menu-list">
                @foreach($settingMenuItems as $menuItem)
                    <li>
                        @php
                            $isActive = $menuItem['active'] ?? false;
                        @endphp
                        <a href="{{ $menuItem['href'] }}" @class(['account-menu-link', 'active' => $isActive]) @if($isActive) aria-current="page" @endif>
                            {{ $menuItem['label'] }}
                        </a>
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
@php
    $trigger = $attributes->get('trigger', 'bottom');
    $dropdownId = 'mobileAccountMenu' . ucfirst($trigger);
    $buttonId = 'mobileAccountMenuButton' . ucfirst($trigger);
@endphp

<div x-data="{ 
    open: false,
    isBottom: '{{ $trigger }}' === 'bottom',
    isTop: '{{ $trigger }}' === 'top'
}" 
@click.outside="if (!$event.target.closest('.mobile-top-profile-btn')) open = false" 
@keydown.escape.window="open = false" 
@profile-menu-toggle.window="if(isBottom) open = !open"
@profile-menu-close.window="open = false"
class="mobile-account-menu-wrap relative">
    <button
        type="button"
        {{ $attributes->merge(['class' => $trigger === 'top' ? 'mobile-top-profile-btn' : 'mobile-nav-item']) }}
        @click="isTop ? $dispatch('profile-menu-toggle') : open = !open; if (isTop || open) document.querySelectorAll('.mobile-menu-overlay').forEach(el => el.classList.remove('is-open'));"
        id="{{ $buttonId }}"
        aria-label="Account menu"
        :aria-expanded="open.toString()"
    >
        @if($user->user_image)
            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="{{ $trigger === 'top' ? 'size-8' : 'size-6' }} rounded-full object-cover" />
        @else
            <x-far-user class="icon {{ $trigger === 'top' ? 'size-6' : 'size-5' }}"/>
        @endif
        @if($trigger === 'bottom')
            <span>Profile</span>
        @endif
    </button>

    {{-- Dropdown Menu (Desktop Style) - Only rendered for bottom trigger as per logic --}}
    @if($trigger === 'bottom')
    {{-- Invisible backdrop for mobile touch devices (iOS Safari click.outside workaround) --}}
    <template x-teleport="body">
        <div 
            x-show="open" 
            @click="open = false"
            class="fixed inset-0 z-[60]"
            x-cloak
        ></div>
    </template>

    <div
        id="{{ $dropdownId }}"
        x-show="open"
        x-cloak
        class="account-menu-dropdown absolute z-[70] bottom-full mb-2 right-0"
        style="background-color: var(--background-color); color: var(--text-color); font-family: var(--font-family);"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
    >
        <div class="px-4 py-3 border-b" style="border-color: color-mix(in srgb, var(--text-color) 10%, transparent)">
            <p class="text-sm font-bold truncate">{{ $user->name }}</p>
            <p class="text-xs flex items-center gap-1 mt-0.5" style="color: color-mix(in srgb, var(--text-color) 60%, transparent)">
                Verified <x-fas-check-circle class="size-3" style="color: var(--accent-color)" />
            </p>
        </div>

        <ul class="account-menu-list">
            @foreach($profileMenuItems as $menuItem)
                @php
                    $isActive = $menuItem['active'] ?? false;
                @endphp
                <li>
                    <a href="{{ $menuItem['href'] }}" @class(['account-menu-link', 'active' => $isActive])>
                        {{ $menuItem['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        @if($isAdmin)
            <div class="account-menu-section">
                <ul class="account-menu-list">
                    @foreach($adminMenuItems as $menuItem)
                        @php
                            $isActive = $menuItem['active'] ?? false;
                        @endphp
                        <li>
                            <a href="{{ $menuItem['href'] }}" @class(['account-menu-link', 'active' => $isActive])>
                                {{ $menuItem['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="account-menu-section">
            <p class="account-menu-heading">Setting</p>
            <ul class="account-menu-list">
                @foreach($settingMenuItems as $menuItem)
                    @php
                        $isActive = $menuItem['active'] ?? false;
                    @endphp
                    <li>
                        <a href="{{ $menuItem['href'] }}" @class(['account-menu-link', 'active' => $isActive])>
                            {{ $menuItem['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="account-menu-logout">
            @csrf
            <button type="submit" class="account-menu-link account-menu-action" style="color: #ef4444">
                Logout
            </button>
        </form>
    </div>
    @endif
</div>
@endmobile
