@php
    $user = Auth::user();
    $isAdmin = $user?->role === 'admin';
    $request = request();

    $profileMenuItems = [
        ['label' => 'View Profile', 'href' => route('profile-detail'), 'active' => $request->routeIs('profile-detail') && (! $request->route('slug') || $request->route('slug') === $user?->slug)],
        ['label' => 'Saved Post', 'href' => route('saved-post'), 'active' => $request->routeIs('saved-post')],
    ];

    $adminMenuItems = $isAdmin
        ? [
            ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => $request->routeIs('dashboard')],
            ['label' => 'Users', 'href' => route('users'), 'active' => $request->routeIs('users')],
            ['label' => 'Reports', 'href' => route('reports'), 'active' => $request->routeIs('reports')],
        ]
        : [];

    $settingMenuItems = [
        ['label' => 'Edit Profile', 'href' => route('edit-profile'), 'active' => $request->routeIs('edit-profile')],
        ['label' => 'Appearance', 'href' => route('appearance'), 'active' => $request->routeIs('appearance')],
        ['label' => 'Edit Tag', 'href' => route('edit-tag'), 'active' => $request->routeIs('edit-tag')],
        ['label' => 'Security', 'href' => route('security'), 'active' => $request->routeIs('security')],
    ];
@endphp

<div x-data="{ open: false, trigger: '{{ $trigger ?? 'top' }}' }" @click.away="open = false" @keydown.escape.window="open = false" class="relative">
    {{-- Trigger Buttons --}}
    <div class="hidden md:block">
        <button
            type="button"
            {{ $attributes->merge(['class' => 'account-button']) }}
            @click="open = !open"
            id="accountMenuButton"
            aria-label="Account menu"
            :aria-expanded="open.toString()"
            aria-controls="accountMenu"
        >
            @if($user && $user->user_image)
                <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="size-8 rounded-full object-cover" />
            @else
                <x-far-user class="icon" />
            @endif
            @if($user)
                <span class="account-text">
                    <span class="verified-label">
                        Verified <x-fas-check-circle class="inline-block size-3" style="color: var(--accent-color);" />
                    </span>
                    <span class="account-name">{{ $user->name }}</span>
                </span>
            @endif
        </button>
    </div>

    <div class="md:hidden">
        <button
            type="button"
            {{ $attributes->merge(['class' => 'mobile-nav-item']) }}
            @click="open = !open"
            id="mobileAccountMenuButton"
            aria-label="Account menu"
            :aria-expanded="open.toString()"
            aria-controls="accountMenu"
        >
            @if($user && $user->user_image)
                <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="size-6 rounded-full object-cover" />
            @else
                <x-far-user class="icon"/>
            @endif
            <span>Profile</span>
        </button>
    </div>

    {{-- Unified Dropdown Menu --}}
    <div
        id="accountMenu"
        x-show="open"
        x-cloak
        class="account-menu-dropdown absolute right-0 z-[100]"
        :class="{ 
            'bottom-full mb-2': trigger === 'bottom', 
            'top-full mt-2': trigger === 'top' 
        }"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        role="menu"
        aria-labelledby="accountMenuButton"
    >
        <ul class="account-menu-list">
            @foreach($profileMenuItems as $item)
                <li>
                    <a
                        href="{{ $item['href'] }}"
                        @class(['account-menu-link', 'active' => $item['active']])
                        @if($item['active']) aria-current="page" @endif
                        role="menuitem"
                    >{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>

        @if(!empty($adminMenuItems))
            <div class="account-menu-section">
                <ul class="account-menu-list">
                    @foreach($adminMenuItems as $item)
                        <li>
                            <a
                                href="{{ $item['href'] }}"
                                @class(['account-menu-link', 'active' => $item['active']])
                                @if($item['active']) aria-current="page" @endif
                                role="menuitem"
                            >{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="account-menu-section">
            <p class="account-menu-heading">Setting</p>
            <ul class="account-menu-list">
                @foreach($settingMenuItems as $item)
                    <li>
                        <a
                            href="{{ $item['href'] }}"
                            @class(['account-menu-link', 'active' => $item['active']])
                            @if($item['active']) aria-current="page" @endif
                            role="menuitem"
                        >{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="account-menu-logout">
            @csrf
            <button type="submit" class="account-menu-link account-menu-action" role="menuitem">Logout</button>
        </form>
    </div>
</div>
