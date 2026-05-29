@desktop
<div class="desktop-action">
    <button
        type="button"
        {{ $attributes->merge(['class' => 'account-button']) }}
        data-menu-target="desktopAccountMenu"
        aria-label="Account menu"
        aria-expanded="false"
    >
        @if(Auth::user()->user_image)
            <img src="{{ Auth::user()->getAvatarUrl() }}" alt="{{ Auth::user()->name }}" class="size-8 rounded-full object-cover" />
        @else
            <x-far-user class="icon" />
        @endif
        <span class="account-text">
            <span class="verified-label">
                Verified <x-fas-check-circle class="inline-block size-3" style="color: var(--accent-color);" />
            </span>
            <span class="account-name">{{ Auth::user()->name }}</span>
        </span>
    </button>
</div>
@enddesktop

@mobile
<a href="#" {{ $attributes->merge(['class' => 'mobile-nav-item']) }}>
    @if(Auth::user()->user_image)
        <img src="{{ Auth::user()->getAvatarUrl() }}" alt="{{ Auth::user()->name }}" class="size-6 rounded-full object-cover" />
    @else
        <x-far-user class="icon"/>
    @endif
    <span class="">Profile</span>
</a>
@endmobile
