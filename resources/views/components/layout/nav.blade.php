<nav class="desktop-nav" aria-label="Primary desktop navigation">
{{--    left navigation section--}}
    <div class="desktop-left">
{{--        Logo and title--}}
        <a href="#" class="brand">
            <x-app-logo/>
            <span>SiteSphere</span>
        </a>

{{--        Search bar--}}
        <label class="desktop-search">
            <x-fas-search class="size-5"/>
            <input type="search" placeholder="Search..." aria-label="Search" />
        </label>
    </div>

{{--    Center Path--}}
    <ul class="desktop-center">
{{--        Home Nav--}}
        <li>
            <a href="#" class="desktop-link active">
               <x-fas-home class="size-5"/>
                Home
            </a>
        </li>
{{--        Category Nav--}}
        <li class="desktop-menu-item">
{{--            Drop down category--}}
            <button
                type="button"
                class="desktop-link"
                data-menu-target="desktopCategoryMenu"
                aria-expanded="false"
            >
                <x-fas-layer-group class="size-5"/>

                Categories
                <x-fas-chevron-down class="size-5"/>
            </button>

{{--            drop down box--}}
            <div
                class="dropdown category-dropdown"
                id="desktopCategoryMenu"
                aria-label="Categories"
            >
                <a href="#" class="dropdown-card">
                    <i class="fas fa-microchip" aria-hidden="true"></i>
                    <span><b>Tech</b><span>Next-gen gadgets</span></span>
                </a>
                <a href="#" class="dropdown-card">
                    <i class="fas fa-gamepad" aria-hidden="true"></i>
                    <span><b>Gaming</b><span>Top tier reviews</span></span>
                </a>
                <a href="#" class="dropdown-card">
                    <i class="fas fa-code" aria-hidden="true"></i>
                    <span><b>Dev Tools</b><span>Build better apps</span></span>
                </a>
                <a href="#" class="dropdown-card">
                    <i class="fas fa-brain" aria-hidden="true"></i>
                    <span><b>AI Models</b><span>LLM tracking</span></span>
                </a>
            </div>
        </li>
    </ul>
{{-- Nav Right Path--}}
    <div class="desktop-right">

{{--            Write Review Path--}}
        <div class="desktop-action">
            <a href="#" class="write-button" aria-label="Write review">
                <x-fas-plus class="size-5 "/>
            </a>
        </div>

{{--        Notification Path--}}
        <div class="desktop-action">
            <span class="action-label">Notifications</span>
            <button
                type="button"
                class="icon-button"
                data-menu-target="desktopNotificationMenu"
                aria-label="Notifications"
                aria-expanded="false"
            >
                <x-far-bell class="size-5" aria-hidden="true"/>
                <span class="badge">3</span>
            </button>

            <div class="dropdown" id="desktopNotificationMenu">
                <p class="dropdown-title">Notifications</p>
                <hr class="dropdown-divider" />
                <p>You have 3 new updates.</p>
            </div>
        </div>

{{--        Account Path--}}
        <div class="desktop-action">
            @auth
                <span class="action-label">Account</span>
                <button
                    type="button"
                    class="account-button"
                    data-menu-target="desktopAccountMenu"
                    aria-label="Account menu"
                    aria-expanded="false"
                >
                    <i class="far fa-user-circle" aria-hidden="true"></i>
                    <span class="account-text">
              <span class="verified-label">
                Verified <i class="fas fa-check-circle" aria-hidden="true"></i>
              </span>
              <span class="account-name">Ko Kyar Pauk</span>
            </span>
                </button>

                <div class="dropdown" id="desktopAccountMenu">
                    <a href="#" class="dropdown-card"><b>Profile</b></a>
                    <a href="#" class="dropdown-card"><b>Logout</b></a>
                </div>
            @endauth
            @guest
                <div>
                    <a href="{{ route('login') }}" class="desktop-link">Login</a>
                    <a href="{{ route('register') }}" class="desktop-link">Register</a>
                </div>
            @endguest
        </div>
    </div>
</nav>
