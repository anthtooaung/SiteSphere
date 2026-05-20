@desktop
<nav class="desktop-nav flex items-center" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" :class="{ 'scrolled': scrolled }">
    <div class="max-w-screen-xl w-full mx-auto flex flex-wrap items-center justify-between">
{{--        left path--}}
        <div class="flex gap-3">
            <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
                <x-app-logo></x-app-logo>
                <span class="self-center text-xl text-heading font-semibold whitespace-nowrap " style="color: var(--accent-color, #6c5ce7);">SiteSphere</span>
            </a>
           @auth
                <x-search-btn></x-search-btn>
           @endauth
        </div>

{{--        center path--}}
        <div class="items-center justify-between w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
            <ul class="flex p-4 md:p-0 mt-4 md:space-x-8 rtl:space-x-reverse md:mt-0 ">
                @auth
                    <li>
                        <x-home-btn/>
                    </li>
                    <li>
                        <x-category-btn />
                    </li>
                @endauth
                @guest
                    <li>
                        <x-home-btn/>
                    </li>
                    <li>
                        <x-about-btn />
                    </li>
                @endguest
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
               @endauth
                @guest

                    <li>
                        <x-login-out-menu-btn/>
                    </li>
                @endguest
            </ul>
        </div>
    </div>


</nav>
@enddesktop

@mobile
    <!-- Mobile Header -->
    <header class="mobile-header" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" :class="{ 'scrolled': scrolled }">
        <a href="#" class="brand">
            <x-app-logo class="size-6"></x-app-logo>
            <span>SiteSphere</span>
        </a>
        @auth
            <div class="mobile-user-pill">
                <x-fas-check-circle class="size-4" style="color: var(--accent-color);" />
                <span>{{ Auth::user()->name }}</span>
            </div>
        @endauth
        @guest
           <x-login-out-menu-btn/>
        @endguest
    </header>

    <!-- Mobile Search (only for authenticated users) -->
    @auth
        <section class="mobile-search" aria-label="Mobile search">
            <form method="post" id="mobileSearchForm" class="w-full">
                @csrf
                <label class="mobile-search-inner">
                    <x-fas-search class="icon"/>
                    <input
                        type="search"
                        placeholder="Search reviews..."
                        aria-label="Search reviews"
                    />
                </label>
            </form>
        </section>
    @endauth

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav" aria-label="Primary mobile navigation">
        <!-- Home Button -->
        <x-home-btn class="active" />

        @auth
            <!-- Categories Trigger -->
            <x-category-btn />

            <!-- Create Post Button -->
            <x-create-post-btn />

            <!-- Notifications Button -->
            <x-noti-btn />

            <!-- Profile Button -->
            <x-profile-menu-btn />
        @endauth

        @guest
            <!-- About Button -->
            <x-about-btn />

        @endguest
    </nav>

    <!-- Mobile Categories Overlay -->
    @auth
        <div class="mobile-menu-overlay" id="mobileCategoryOverlay">
            <button
                type="button"
                class="mobile-close-button"
                data-mobile-menu-close
                aria-label="Close categories"
            >
                <x-fas-times class="size-8"/>
            </button>
            <a href="#" class="mobile-overlay-link">
                <x-fas-microchip class="icon size-8"/>
                Technology
            </a>
            <a href="#" class="mobile-overlay-link">
                <x-fas-gamepad class="icon size-8"/>
                Gaming
            </a>
            <a href="#" class="mobile-overlay-link">
                <x-fas-brain class="icon size-8"/>
                AI Models
            </a>
            <a href="#" class="mobile-overlay-link">
                <x-fas-gear class="icon size-8"/>
                Settings
            </a>
        </div>
    @endauth

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
