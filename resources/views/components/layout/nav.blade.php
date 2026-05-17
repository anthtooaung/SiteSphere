@desktop
<nav class="bg-neutral-100 fixed w-full z-20 top-0 start-0 border-b border-1 p-3 ">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between ">
{{--        left path--}}
        <div class="flex gap-3">
            <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
                <x-app-logo></x-app-logo>
                <span class="self-center text-xl text-heading font-semibold whitespace-nowrap">SiteSphere</span>
            </a>
            <x-search-btn></x-search-btn>
        </div>

{{--        right path--}}
        <div class="flex md:order-2 space-x-3 md:space-x-3 rtl:space-x-reverse items-center justify-center ">

            <div class="desktop-action">
                <a href="#"
                   class="write-button hover:rotate-180 duration-100"
                   data-tooltip-placement="bottom"
                   type="button"
                   data-tooltip-target="create-post">
                    <x-fas-plus class="icon hover:rotate-180 duration-100"/>
                </a>
                <div id="create-post" role="tooltip" class=" text-white absolute z-10 invisible inline-block px-3 py-2 text-md bg-gray-600 rounded-xl shadow-xs opacity-0 tooltip">
                    Create Post
                </div>
            </div>

            <div class="desktop-action">
                <button
                    data-tooltip-placement="bottom"
                    type="button"
                    data-tooltip-target="notification"
                    class="relative shadow-md rounded-xl p-2"
                >
                    <x-far-bell class="icon"/>
                    <span class="absolute translate-x-1/2 ">3</span>
                </button>
                <div id="notification" role="tooltip" class=" text-white absolute z-10 invisible inline-block px-3 py-2 text-md bg-gray-600 rounded-xl shadow-xs opacity-0 tooltip">
                    Create Post
                </div>
            </div>

            <div class="desktop-action">
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

            </div>
            <button data-collapse-toggle="navbar-sticky" type="button" class="inline-flex md:hidden items-center p-2 w-10 h-10 justify-center text-sm text-body rounded-base hover:bg-neutral-secondary-soft hover:text-heading focus:outline-none focus:ring-2 focus:ring-neutral-tertiary" aria-controls="navbar-sticky" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/></svg>
            </button>
        </div>

{{--        center path--}}
        <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
            <ul class="flex flex-col p-4 md:p-0 mt-4 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 ">
                <li>
                    <a href="#" class="flex text-md items-center justify-center gap-1" aria-current="page">
                        <x-fas-home  class="icon"/>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <button  data-dropdown-toggle="mega-menu-icons-dropdown" class="flex text-md items-center justify-center gap-1">
                        <x-fas-layer-group  class="icon"/>
                        <span>Categories</span>
                        <x-fas-chevron-down class="icon" />
                    </button>
{{--                    mega menu --}}
                    <div id="mega-menu-icons-dropdown" class="absolute z-10 grid hidden w-auto grid-cols-2 text-sm bg-neutral-200 border border-1 rounded-xl shadow-md md:grid-cols-3">
                        <div class="p-4 pb-0 text-heading md:pb-4">
                            <ul class="space-y-3 font-normal" aria-labelledby="mega-menu-icons-dropdown-button">
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        About Us
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.03v13m0-13c-2.819-.831-4.715-1.076-8.029-1.023A.99.99 0 0 0 3 6v11c0 .563.466 1.014 1.03 1.007 3.122-.043 5.018.212 7.97 1.023m0-13c2.819-.831 4.715-1.076 8.029-1.023A.99.99 0 0 1 21 6v11c0 .563-.466 1.014-1.03 1.007-3.122-.043-5.018.212-7.97 1.023"/></svg>
                                        Library
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11H4m15.5 5a.5.5 0 0 0 .5-.5V8a1 1 0 0 0-1-1h-3.75a1 1 0 0 1-.829-.44l-1.436-2.12a1 1 0 0 0-.828-.44H8a1 1 0 0 0-1 1M4 9v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-7a1 1 0 0 0-1-1h-3.75a1 1 0 0 1-.829-.44L9.985 8.44A1 1 0 0 0 9.157 8H5a1 1 0 0 0-1 1Z"/></svg>
                                        Resources
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m10.051 8.102-3.778.322-1.994 1.994a.94.94 0 0 0 .533 1.6l2.698.316m8.39 1.617-.322 3.78-1.994 1.994a.94.94 0 0 1-1.595-.533l-.4-2.652m8.166-11.174a1.366 1.366 0 0 0-1.12-1.12c-1.616-.279-4.906-.623-6.38.853-1.671 1.672-5.211 8.015-6.31 10.023a.932.932 0 0 0 .162 1.111l.828.835.833.832a.932.932 0 0 0 1.111.163c2.008-1.102 8.35-4.642 10.021-6.312 1.475-1.478 1.133-4.77.855-6.385Zm-2.961 3.722a1.88 1.88 0 1 1-3.76 0 1.88 1.88 0 0 1 3.76 0Z"/></svg>
                                        Pro Version
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="p-4 pb-0 md:pb-4">
                            <ul class="space-y-3 font-normal">
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m4 8h6m-6-4h6m4-8v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"/></svg>
                                        Blog
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16v-5.5A3.5 3.5 0 0 0 7.5 7m3.5 9H4v-5.5A3.5 3.5 0 0 1 7.5 7m3.5 9v4M7.5 7H14m0 0V4h2.5M14 7v3m-3.5 6H20v-6a3 3 0 0 0-3-3m-2 9v4m-8-6.5h1"/></svg>
                                        Newsletter
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M4.07141 14v6h5.99999v-6H4.07141Zm4.5-4h6.99999l-3.5-6-3.49999 6Zm7.99999 10c1.933 0 3.5-1.567 3.5-3.5s-1.567-3.5-3.5-3.5-3.5 1.567-3.5 3.5 1.567 3.5 3.5 3.5Z"/></svg>
                                        Playground
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.5 21h13M12 21V7m0 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm2-1.8c3.073.661 2.467 2.8 5 2.8M5 8c3.359 0 2.192-2.115 5.012-2.793M7 9.556V7.75m0 1.806-1.95 4.393a.773.773 0 0 0 .37.962.785.785 0 0 0 .362.089h2.436a.785.785 0 0 0 .643-.335.776.776 0 0 0 .09-.716L7 9.556Zm10 0V7.313m0 2.243-1.95 4.393a.773.773 0 0 0 .37.962.786.786 0 0 0 .362.089h2.436a.785.785 0 0 0 .643-.335.775.775 0 0 0 .09-.716L17 9.556Z"/></svg>
                                        License
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="p-4">
                            <ul class="space-y-3 font-normal">
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.427 14.768 17.2 13.542a1.733 1.733 0 0 0-2.45 0l-.613.613a1.732 1.732 0 0 1-2.45 0l-1.838-1.84a1.735 1.735 0 0 1 0-2.452l.612-.613a1.735 1.735 0 0 0 0-2.452L9.237 5.572a1.6 1.6 0 0 0-2.45 0c-3.223 3.2-1.702 6.896 1.519 10.117 3.22 3.221 6.914 4.745 10.12 1.535a1.601 1.601 0 0 0 0-2.456Z"/></svg>
                                        Contact Us
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.529 9.988a2.502 2.502 0 1 1 5 .191A2.441 2.441 0 0 1 12 12.582V14m-.01 3.008H12M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        Support Center
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="inline-flex items-center text-body hover:text-fg-brand">
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 12v6h8v-6H8Z"/></svg>
                                        Terms
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
@enddesktop

@mobile

@endmobile
