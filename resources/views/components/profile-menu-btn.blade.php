@php
    $user = Auth::user();
    $isAdmin = $user?->role === 'admin';
    $isDashboardRoute = request()->routeIs('dashboard');

    $profileMenuItems = [
        ['label' => 'View Profile', 'href' => route('profile-detail'), 'active' => request()->routeIs('profile-detail') && (!request()->route('name') || request()->route('name') === $user?->name)],
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
@click.outside="open = false" 
@keydown.escape.window="open = false" 
@profile-menu-toggle.window="if(isBottom) open = !open"
class="mobile-account-menu-wrap relative">
    <button
        type="button"
        {{ $attributes->merge(['class' => $trigger === 'top' ? 'mobile-top-profile-btn' : 'mobile-nav-item']) }}
        @click="isTop ? $dispatch('profile-menu-toggle') : open = !open"
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
    <div
        id="{{ $dropdownId }}"
        x-show="open"
        x-cloak
        class="account-menu-dropdown absolute z-[70] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 py-2 w-56 bottom-full mb-2 right-0"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
    >
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                Verified <x-fas-check-circle class="size-3 text-indigo-500" />
            </p>
        </div>

        <ul class="account-menu-list py-1">
            @foreach($profileMenuItems as $menuItem)
                @php
                    $isActive = $menuItem['active'] ?? false;
                @endphp
                <li>
                    <a href="{{ $menuItem['href'] }}" @class(['account-menu-link px-4 py-2 block text-sm', 'active' => $isActive])>
                        {{ $menuItem['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        @if($isAdmin)
            <div class="account-menu-section border-t border-gray-100 dark:border-gray-700 py-1">
                <ul class="account-menu-list">
                    @foreach($adminMenuItems as $menuItem)
                        @php
                            $isActive = $menuItem['active'] ?? false;
                        @endphp
                        <li>
                            <a href="{{ $menuItem['href'] }}" @class(['account-menu-link px-4 py-2 block text-sm', 'active' => $isActive])>
                                {{ $menuItem['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="account-menu-section border-t border-gray-100 dark:border-gray-700 py-1">
            <p class="account-menu-heading px-4 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Setting</p>
            <ul class="account-menu-list">
                @foreach($settingMenuItems as $menuItem)
                    @php
                        $isActive = $menuItem['active'] ?? false;
                    @endphp
                    <li>
                        <a href="{{ $menuItem['href'] }}" @class(['account-menu-link px-4 py-2 block text-sm', 'active' => $isActive])>
                            {{ $menuItem['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 dark:border-gray-700 mt-1">
            @csrf
            <button type="submit" class="account-menu-link w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                Logout
            </button>
        </form>
    </div>
    @endif
</div>
@endmobile
