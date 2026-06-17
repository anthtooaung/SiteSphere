@php
    $user = Auth::user();
    $isAdmin = $user?->role === 'admin';
    $isDashboardRoute = request()->routeIs('dashboard');

    $profileMenuItems = [
        ['label' => 'View Profile', 'href' => route('profile-detail'), 'active' => request()->routeIs('profile-detail') && (!request()->route('slug') || request()->route('slug') === $user?->slug)],
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
                @php($isActive = $menuItem['active'] ?? false)
                <li>
                    <a
                        href="{{ $menuItem['href'] }}"
                        @class(['account-menu-link', 'active' => $isActive])
                        @if($isActive) aria-current="page" @endif
                    >{{ $menuItem['label'] }}</a>
                </li>
            @endforeach
        </ul>

        @if($isAdmin)
            <div class="account-menu-section">
                <ul class="account-menu-list">
                    @foreach($adminMenuItems as $menuItem)
                        @php($isActive = $menuItem['active'] ?? false)
                        <li>
                            <a
                                href="{{ $menuItem['href'] }}"
                                @class(['account-menu-link', 'active' => $isActive])
                                @if($isActive) aria-current="page" @endif
                            >{{ $menuItem['label'] }}</a>
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
                        @php($isActive = $menuItem['active'] ?? false)
                        <a
                            href="{{ $menuItem['href'] }}"
                            @class(['account-menu-link', 'active' => $isActive])
                            @if($isActive) aria-current="page" @endif
                        >{{ $menuItem['label'] }}</a>
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
<div x-data="{ open: false }" @keydown.escape.window="open = false" class="mobile-account-menu-wrap">
    <button
        type="button"
        {{ $attributes->merge(['class' => 'mobile-nav-item']) }}
        @click="open = true"
        aria-label="Account menu"
        :aria-expanded="open.toString()"
    >
        @if($user->user_image)
            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="size-6 rounded-full object-cover" />
        @else
            <x-far-user class="icon"/>
        @endif
        <span>Profile</span>
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-[60]" @click="open = false" x-transition.opacity></div>

    {{-- Bottom Sheet --}}
    <div
        id="mobileAccountMenu"
        x-show="open"
        x-cloak
        class="fixed inset-x-0 bottom-0 z-[70] h-[75vh] bg-white dark:bg-gray-800 rounded-t-[2.5rem] shadow-2xl p-6 overflow-y-auto transform transition-transform duration-300 ease-in-out"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
    >
        {{-- Drawer Handle --}}
        <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full mx-auto mb-6"></div>

        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Account & Settings</h3>
            <button @click="open = false" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-full text-gray-500 dark:text-gray-400">
                <x-fas-xmark class="size-4" />
            </button>
        </div>

        {{-- User Summary --}}
        <div class="flex items-center gap-4 mb-8 pb-4 border-b border-gray-100 dark:border-gray-700">
            @if($user->user_image)
                <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="size-12 rounded-full object-cover" />
            @else
                <div class="size-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <x-far-user class="size-6 text-gray-400" />
                </div>
            @endif
            <div>
                <div class="font-bold text-lg text-gray-900 dark:text-white">{{ $user->name }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                    Verified <x-fas-check-circle class="size-3 text-indigo-500" />
                </div>
            </div>
        </div>

        {{-- Content Grouping --}}
        <div class="space-y-8 pb-8">
            {{-- Profile Section --}}
            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Profile</p>
                <ul class="space-y-4">
                    @foreach($profileMenuItems as $menuItem)
                        <li>
                            <a href="{{ $menuItem['href'] }}" class="flex items-center gap-3 text-gray-700 dark:text-gray-300 font-medium">
                                <span class="w-5 text-center">
                                    @if($menuItem['label'] === 'View Profile') 👤 @else 🔖 @endif
                                </span>
                                {{ $menuItem['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Admin Section --}}
            @if($isAdmin)
                <div>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Admin</p>
                    <ul class="space-y-4">
                        @foreach($adminMenuItems as $menuItem)
                            <li>
                                <a href="{{ $menuItem['href'] }}" class="flex items-center gap-3 text-gray-700 dark:text-gray-300 font-medium">
                                    <span class="w-5 text-center">
                                        @if($menuItem['label'] === 'Dashboard') 📊 @elseif($menuItem['label'] === 'Users') 👥 @else 🚩 @endif
                                    </span>
                                    {{ $menuItem['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Settings Section --}}
            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Settings</p>
                <ul class="space-y-4">
                    @foreach($settingMenuItems as $menuItem)
                        <li>
                            <a href="{{ $menuItem['href'] }}" class="flex items-center gap-3 text-gray-700 dark:text-gray-300 font-medium">
                                <span class="w-5 text-center">
                                    @if($menuItem['label'] === 'Edit Profile') ✏️ @elseif($menuItem['label'] === 'Appearance') 🎨 @elseif($menuItem['label'] === 'Edit Tag') 🏷️ @else 🛡️ @endif
                                </span>
                                {{ $menuItem['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Logout Section --}}
            <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 text-red-500 font-semibold">
                        <span class="w-5 text-center">🚪</span> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endmobile
