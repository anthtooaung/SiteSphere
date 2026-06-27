@desktop
@php
    $isHorizontal = in_array($menuBarLocation ?? 'left', ['top', 'bottom'], true);
@endphp
<nav @class(['desktop-nav hidden md:flex items-center px-4 md:px-6 lg:px-8', 'layout-menu-topbar' => $isHorizontal]) x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" :class="{ 'scrolled': scrolled }">
    <div class="max-w-screen-xl w-full mx-auto flex flex-wrap items-center justify-between gap-4">
{{--        left path--}}
        <div class="flex gap-4 items-center">
            <a href="{{ route('welcome') }}" class="site-brand flex items-center space-x-0 rtl:space-x-reverse">
                <x-app-logo></x-app-logo>
                <span class="self-center text-xl text-heading font-semibold whitespace-nowrap">SiteSphere</span>
            </a>
            @if(!request()->routeIs('welcome'))
                <div class="hidden lg:block ml-2">
                    <x-search-btn />
                </div>
            @endif
        </div>

{{--        center path--}}
        <div class="items-center justify-between w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
            <ul class="flex p-4 md:p-0 mt-4 md:gap-8 rtl:space-x-reverse md:mt-0 ">
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
        <div class="flex items-center md:order-2 md:gap-4 rtl:space-x-reverse">
            <ul class="flex items-center justify-center p-4 md:p-0 mt-4 md:gap-4 rtl:space-x-reverse md:mt-0 ">
               @auth
                    <li>
                        <x-create-post-btn />
                    </li>
                    <li class="hidden md:block">
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
    <header class="mobile-header {{ request()->routeIs(['welcome', 'about-us']) ? '!fixed w-full' : 'sticky' }} top-0 z-50 flex items-center justify-between px-4 py-3" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" :class="{ 'scrolled': scrolled }">
        <a href="{{ route('welcome') }}" class="flex items-center gap-2">
            <x-app-logo class="size-6"></x-app-logo>
            <span class="font-bold text-lg">SiteSphere</span>
        </a>

        @if(request()->routeIs(['welcome', 'about-us']))
            <x-noti-btn trigger="top" mobile-mode="trigger" />
        @else
            @auth
                <x-search-btn compact />
            @else
                <x-login-out-menu-btn trigger="top" />
            @endauth
        @endif
    </header>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav z-50 md:hidden" style="background-color: var(--background-color);">
        <x-home-btn />
        <x-category-btn mobile-mode="trigger" />
        @auth
            <x-create-post-btn />
        @endauth
        @if(request()->routeIs(['welcome', 'about-us']))
            <x-about-btn />
        @else
            <x-noti-btn mobile-mode="trigger" />
        @endif
        @auth
            <x-profile-menu-btn trigger="bottom" />
        @else
            <x-login-out-menu-btn trigger="bottom" />
        @endauth
    </nav>

    <x-category-btn mobile-mode="overlay" />
    <x-noti-btn mobile-mode="overlay" />

    <!-- Mobile Navigation Interactions Script -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Category Overlay Logic
            const categoryOverlay = document.getElementById("mobileCategoryOverlay");
            const openCategoryBtn = document.querySelector("[data-mobile-menu-open]");
            const closeCategoryBtn = document.querySelector("[data-mobile-menu-close]");

            if (openCategoryBtn && categoryOverlay) {
                openCategoryBtn.addEventListener("click", () => {
                    categoryOverlay.classList.add("is-open");
                    if (notiOverlay) notiOverlay.classList.remove("is-open");
                    window.dispatchEvent(new CustomEvent('profile-menu-close'));
                });
            }

            if (closeCategoryBtn && categoryOverlay) {
                closeCategoryBtn.addEventListener("click", () => {
                    categoryOverlay.classList.remove("is-open");
                });
            }

            // Notification Overlay Logic
            const notiOverlay = document.getElementById("mobileNotiOverlay");
            const openNotiBtn = document.querySelector("[data-mobile-noti-open]");
            const closeNotiBtn = document.querySelector("[data-mobile-noti-close]");

            if (openNotiBtn && notiOverlay) {
                openNotiBtn.addEventListener("click", () => {
                    notiOverlay.classList.add("is-open");
                    if (categoryOverlay) categoryOverlay.classList.remove("is-open");
                    window.dispatchEvent(new CustomEvent('profile-menu-close'));
                });
            }

            if (closeNotiBtn && notiOverlay) {
                closeNotiBtn.addEventListener("click", () => {
                    notiOverlay.classList.remove("is-open");
                });
            }

            const mobileButtons = document.querySelectorAll(".mobile-bottom-nav .mobile-nav-item, .mobile-bottom-nav .mobile-add-button");

            mobileButtons.forEach((button) => {
                button.addEventListener("click", () => {
                    button.classList.add("is-pressed");
                    setTimeout(() => button.classList.remove("is-pressed"), 120);
                });
            });
        });
    </script>
@endmobile
