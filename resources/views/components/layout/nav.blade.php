@php
    $isLanding = request()->routeIs(['welcome', 'about-us']);
@endphp

<div class="nav-container">
    {{-- Unified Nav structure for testing --}}
    <div class="hidden md:block">
        <nav class="desktop-nav flex items-center" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" :class="{ 'scrolled': scrolled }">
            <div class="max-w-screen-xl w-full mx-auto flex flex-wrap items-center justify-between">
                <div class="flex gap-3 items-center">
                    <a href="{{ route('welcome') }}" class="site-brand flex items-center space-x-0 rtl:space-x-reverse">
                        <x-app-logo></x-app-logo>
                        <span class="self-center text-xl text-heading font-semibold whitespace-nowrap">SiteSphere</span>
                    </a>
                    <div class="hidden md:block ml-4">
                        <x-search-btn />
                    </div>
                </div>

                <div class="items-center justify-between w-full md:flex md:w-auto md:order-1">
                    <ul class="flex p-4 md:p-0 mt-4 md:space-x-8 rtl:space-x-reverse md:mt-0 ">
                        <li><x-home-btn/></li>
                        <li><x-category-btn /></li>
                        @if($isLanding)
                            <li><x-about-btn /></li>
                        @endif
                    </ul>
                </div>

                <div class="flex md:order-2 space-x-3 md:space-x-3 rtl:space-x-reverse">
                    <ul class="flex items-center justify-center p-4 md:p-0 mt-4 md:space-x-4 rtl:space-x-reverse md:mt-0 ">
                       @auth
                            <li><x-create-post-btn /></li>
                            <li><x-noti-btn /></li>
                            <li><x-profile-menu-btn trigger="top" /></li>
                       @else
                            <li><x-login-out-menu-btn/></li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </div>

@mobile
    <div class="md:hidden" x-data="{ categoryOpen: false }">
        <!-- Mobile Header -->
        <header class="mobile-header flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2">
                <x-app-logo class="size-6"></x-app-logo>
                <span class="font-bold text-lg dark:text-white">SiteSphere</span>
            </a>

            @if($isLanding)
                @auth
                    <x-profile-menu-btn trigger="top" />
                @else
                    <x-login-out-menu-btn />
                @endauth
            @endif
        </header>

        <!-- Mobile Bottom Navigation Bar -->
        <nav class="mobile-bottom-nav fixed bottom-0 inset-x-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 flex justify-around items-center py-2 z-50">
            <x-home-btn />
            <x-category-btn mobile-mode="trigger" @click="categoryOpen = true" data-mobile-menu-open />
            <x-create-post-btn />
            <x-noti-btn />
            @if(!$isLanding)
                @auth
                    <x-profile-menu-btn trigger="bottom" />
                @else
                    <x-login-out-menu-btn />
                @endauth
            @endif
        </nav>

        <x-category-btn mobile-mode="overlay" x-bind:class="{ 'is-open': categoryOpen }" @close-category="categoryOpen = false" />
        </div>
        @endmobile

