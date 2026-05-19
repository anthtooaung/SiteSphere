@desktop
<nav class="bg-neutral-100 fixed w-full z-20 top-0 start-0 border-b border-1 p-3 ">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between ">
{{--        left path--}}
        <div class="flex gap-3">
            <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
                <x-app-logo></x-app-logo>
                <span class="self-center text-xl text-heading font-semibold whitespace-nowrap">SiteSphere</span>
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

@endmobile
