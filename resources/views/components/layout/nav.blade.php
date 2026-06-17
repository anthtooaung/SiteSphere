@desktop
<nav class="desktop-nav flex items-center" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" :class="{ 'scrolled': scrolled }">
    <div class="max-w-screen-xl w-full mx-auto flex flex-wrap items-center justify-between">
{{--        left path--}}
        <div class="flex gap-3 items-center">
            <a href="{{ route('welcome') }}" class="site-brand flex items-center space-x-0 rtl:space-x-reverse">
                <x-app-logo></x-app-logo>
                <span class="self-center text-xl text-heading font-semibold whitespace-nowrap">SiteSphere</span>
            </a>
            <div class="hidden md:block ml-4">
                <x-search-btn />
            </div>
        </div>

{{--        center path--}}
        <div class="items-center justify-between w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
            <ul class="flex p-4 md:p-0 mt-4 md:space-x-8 rtl:space-x-reverse md:mt-0 ">
                <li>
                    <x-home-btn/>
                </li>
                <li>
                    <x-category-btn />
                </li>
                @if(request()->routeIs(['welcome', 'about-us']))
                    <li>
                        <x-about-btn />
                    </li>
                @endif
            </ul>
        </div>

        {{--        right path--}}
        <div class="flex md:order-2 space-x-3 md:space-x-3 rtl:space-x-reverse  ">
            <ul class="flex items-center justify-center p-4 md:p-0 mt-4 md:space-x-4 rtl:space-x-reverse md:mt-0 ">
               @auth
                    <li>
                        <x-create-post-btn />
                    </li>
                    <li>
                        <x-noti-btn />
                    </li>
                    <li>
                        <x-profile-menu-btn />
                    </li>
               @else
                    <li>
                        <x-login-out-menu-btn/>
                    </li>
                @endauth
            </ul>
        </div>
    </div>


</nav>
@enddesktop

@mobile
    <!-- Mobile Header -->
    <header class="mobile-header" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" :class="{ 'scrolled': scrolled }">
        <a href="{{ route('welcome') }}" class="brand site-brand">
            <x-app-logo class="size-6"></x-app-logo>
            <span>SiteSphere</span>
        </a>

        @if(request()->routeIs(['welcome', 'about-us']))
            @auth
                <x-profile-menu-btn class="auth-menu-button" />
            @else
                <x-login-out-menu-btn class="auth-menu-button" data-dropdown-placement="bottom-end" />
            @endauth
        @endif
    </header>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav" aria-label="Primary mobile navigation">
        <!-- Home Button -->
        <x-home-btn />

        <!-- Categories Trigger -->
        <x-category-btn mobile-mode="trigger" />

        @if(request()->routeIs(['welcome', 'about-us']))
            @auth
                <!-- Upload Button -->
                <x-create-post-btn />

                <!-- About Button -->
                <x-about-btn />

                <!-- Notification Button -->
                <x-noti-btn />
            @else
                <!-- About Button -->
                <x-about-btn />
            @endauth
        @else
            @auth
                <!-- Upload Button -->
                <x-create-post-btn />

                <!-- Notification Button -->
                <x-noti-btn />

                <!-- Profile Button -->
                <x-profile-menu-btn />
            @else
                <!-- Login Button -->
                <x-login-out-menu-btn />
            @endauth
        @endif
    </nav>

    <x-category-btn mobile-mode="overlay" />

    <!-- Mobile Navigation Interactions Script -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const mobileOverlay = document.getElementById("mobileCategoryOverlay");
            const openBtn = document.querySelector("[data-mobile-menu-open]");
            const closeBtn = document.querySelector("[data-mobile-menu-close]");

            if (openBtn && mobileOverlay) {
                openBtn.addEventListener("click", () => {
                    mobileOverlay.classList.add("is-open");
                });
            }

            if (closeBtn && mobileOverlay) {
                closeBtn.addEventListener("click", () => {
                    mobileOverlay.classList.remove("is-open");
                });
            }

            const mobileButtons = document.querySelectorAll(".mobile-bottom-nav .mobile-nav-item, .mobile-bottom-nav .mobile-add-button");

            mobileButtons.forEach((button) => {
                button.addEventListener("click", () => {
                    mobileButtons.forEach(btn => btn.classList.remove("active"));
                    button.classList.add("active");

                    button.classList.add("is-pressed");
                    setTimeout(() => button.classList.remove("is-pressed"), 120);
                });
            });
        });
    </script>
@endmobile
