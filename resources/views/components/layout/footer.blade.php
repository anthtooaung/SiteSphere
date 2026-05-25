<footer class="relative overflow-hidden bg-slate-900 px-6 py-16 font-sans text-slate-200 sm:px-10 md:px-16">
    <div class="pointer-events-none absolute inset-0 hidden opacity-10 md:block" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-96 w-96 animate-pulse rounded-full bg-white mix-blend-screen blur-[120px] duration-[6s]"></div>
        <div class="absolute -bottom-32 -left-32 h-[500px] w-[500px] rounded-full bg-slate-400 opacity-30 blur-[150px]"></div>
        <svg class="absolute right-12 top-0 h-full w-1/4 text-white opacity-10" fill="none" viewBox="0 0 400 400">
            <circle cx="200" cy="200" r="160" stroke="currentColor" stroke-dasharray="8 8" stroke-width="1.5" class="animate-[spin_120s_linear_infinite]"></circle>
            <circle cx="200" cy="200" r="110" stroke="currentColor" stroke-width="0.75"></circle>
        </svg>
    </div>

    <div class="relative z-10 mx-auto max-w-5xl">
        <div class="mb-16 flex flex-col items-center text-center md:items-start md:text-left">
            <a href="{{ route('welcome') }}" class="group mb-4 flex items-center" aria-label="SiteSphere home">
                <span class="relative overflow-hidden pb-1 text-3xl font-black tracking-normal text-white">
                    SiteSphere
                    <span class="absolute bottom-0 left-0 h-[2px] w-full -translate-x-full bg-white transition-transform duration-300 group-hover:translate-x-0"></span>
                </span>
            </a>
            <p class="max-w-xl text-sm leading-relaxed text-slate-400">
                Empowering better resource choices. Join our global community of trusted website and honest tool reviewers.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-12 border-b border-slate-800 pb-12 sm:grid-cols-2 lg:grid-cols-3">
            <div class="text-center sm:text-left">
                <h4 class="relative mb-5 inline-block text-xs font-bold uppercase tracking-widest text-white">
                    Explore
                    <span class="absolute -bottom-1.5 left-1/2 h-[2px] w-6 -translate-x-1/2 rounded bg-slate-500 sm:left-0 sm:translate-x-0"></span>
                </h4>
                <ul class="flex flex-col items-center gap-3.5 text-sm sm:items-start">
                    <li>
                        <a href="{{ route('home') }}" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                            <x-fas-layer-group class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                            <span>Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                            <x-fas-clock class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                            <span>Latest Reviews</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                            <x-fas-star class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                            <span>Top Rated Software</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                            <x-fas-user-pen class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                            <span>Write A Review</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="text-center sm:text-left">
                <h4 class="relative mb-5 inline-block text-xs font-bold uppercase tracking-widest text-white">
                    Community
                    <span class="absolute -bottom-1.5 left-1/2 h-[2px] w-6 -translate-x-1/2 rounded bg-slate-500 sm:left-0 sm:translate-x-0"></span>
                </h4>
                <ul class="flex flex-col items-center gap-3.5 text-sm sm:items-start">
                    <li>
                        <a href="{{ route('welcome') }}" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                            <x-fas-circle-info class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                            <span>About Us</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:hello@sitesphere.test" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                            <x-fas-envelope class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                            <span>Contact Us</span>
                        </a>
                    </li>
                    @auth
                        <li>
                            <a href="{{ route('dashboard') }}" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                                <x-fas-gauge-high class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                                <span>Dashboard</span>
                            </a>
                        </li>
                    @endauth
                    @guest
                        <li>
                            <a href="{{ route('login') }}" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                                <x-fas-right-to-bracket class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                                <span>Log In</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}" class="group flex items-center gap-2.5 py-0.5 text-slate-400 transition-all duration-300 hover:translate-x-1.5 hover:text-white">
                                <x-fas-user-plus class="w-4 text-center text-xs text-slate-600 transition-all group-hover:scale-110 group-hover:text-white" />
                                <span>Sign Up</span>
                            </a>
                        </li>
                    @endguest
                </ul>
            </div>

            <div class="flex flex-col items-center gap-6 sm:col-span-2 sm:items-start lg:col-span-1">
                <div class="w-full text-center sm:text-left">
                    <h4 class="mb-4 text-xs font-bold uppercase tracking-widest text-white">Connect with Us</h4>
                    <div class="flex justify-center gap-3.5 sm:justify-start">
                        <a href="https://www.linkedin.com" class="flex h-9 w-9 -translate-y-0 items-center justify-center rounded-xl bg-white/10 text-base text-white transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:text-slate-900 hover:shadow-lg" aria-label="LinkedIn">
                            <x-fab-linkedin-in class="size-4" />
                        </a>
                        <a href="https://telegram.org" class="flex h-9 w-9 -translate-y-0 items-center justify-center rounded-xl bg-white/10 text-base text-white transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:text-slate-900 hover:shadow-lg" aria-label="Telegram">
                            <x-fab-telegram class="size-4" />
                        </a>
                        <a href="https://github.com" class="flex h-9 w-9 -translate-y-0 items-center justify-center rounded-xl bg-white/10 text-base text-white transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:text-slate-900 hover:shadow-lg" aria-label="GitHub">
                            <x-fab-github class="size-4" />
                        </a>
                    </div>
                </div>

                <div class="w-full rounded-2xl border border-white/10 bg-white/5 p-5 shadow-xl backdrop-blur-md">
                    <label for="footer-newsletter-email" class="mb-2.5 block text-center text-xs font-bold uppercase tracking-wider text-slate-300 sm:text-left">
                        Newsletter
                    </label>
                    <div class="space-y-2">
                        <input id="footer-newsletter-email" type="email" placeholder="Email Address" class="w-full rounded-xl border border-white/10 bg-slate-950/40 px-3 py-2.5 text-sm text-white transition-all placeholder-white/30 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-white/5">
                        <button type="button" class="w-full rounded-xl bg-white py-2.5 text-xs font-bold uppercase tracking-wider text-slate-900 shadow-md transition-all duration-300 hover:bg-slate-100 active:scale-[0.98]">
                            Subscribe
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-between pt-8 text-xs sm:flex-row">
            <p class="text-center text-slate-500 sm:text-left">&copy; {{ date('Y') }} SiteSphere. All Rights Reserved.</p>
        </div>
    </div>
</footer>
