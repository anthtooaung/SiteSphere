@extends('index')
@section('title')
About Us
@endsection

@push('styles')
@vite('resources/css/about-us.css')
<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none !important;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    html,
    body {
        -ms-overflow-style: none !important;
        /* IE and Edge */
        scrollbar-width: none !important;
        /* Firefox */
    }
</style>
@endpush

@push('scripts')
@vite('resources/js/about-us.js')
@endpush

@section('theme-style')
<style>
    :root {
        --accent-color: #6c5ce7;
        --background-color: #ffffff;
        --text-color: #0d1b2a;
        --font-family: {!! $fontFamily ?? 'Figtree, sans-serif' !!};
    }
</style>
@overwrite

@section('content')
<x-layout.nav />
<div class="md:mt-24"></div>

<main class="aboutus-main">
    {{-- Hero Section --}}
    <header class="aboutus-hero" id="top">
        <div class="aboutus-container aboutus-hero-grid">
            <div class="aboutus-hero-content aboutus-scroll-reveal">
                <span class="aboutus-eyebrow">
                    <x-fas-shield-halved class="icon" aria-hidden="true" style="width:0.72rem;height:0.72rem;" />
                    About SiteSphere
                </span>
                <h1>Discover the best tools, <span>without the hassle.</span></h1>

                <div class="aboutus-hero-body-content">
                    <p class="aboutus-hero-copy">SiteSphere is a community-driven platform built for developers and creators to discover, share, and rate the best websites, tools, and libraries. Instead of searching through countless docs and videos, find trusted recommendations from real users — all in one place.</p>
                </div>

                <div class="aboutus-hero-actions">
                    <a href="#metrics" class="aboutus-btn aboutus-btn-primary">
                        <x-fas-magnifying-glass class="icon" aria-hidden="true" style="width:0.9rem;height:0.9rem;" />
                        Explore Core Values
                    </a>
                    <a href="#team" class="aboutus-btn aboutus-btn-ghost">
                        <x-fas-users class="icon" aria-hidden="true" style="width:0.9rem;height:0.9rem;" />
                        Meet Our Team
                    </a>
                </div>
            </div>

            {{-- Side Panel --}}
            <aside class="aboutus-hero-panel aboutus-scroll-reveal" aria-label="SiteSphere platform features">
                <div class="aboutus-panel-header-wrap">
                    <div class="aboutus-panel-logo">
                        <x-app-logo class="size-6" />
                    </div>
                    <h4>Platform Standards</h4>
                </div>
                <div class="aboutus-panel-list">
                    <div class="aboutus-panel-item">
                        <x-fas-filter class="icon" aria-hidden="true" />
                        <div>
                            <strong>Smart Category Filtering</strong>
                            <span>Browse resources by categories and tags to quickly find the exact tools that fit your needs.</span>
                        </div>
                    </div>
                    <div class="aboutus-panel-item">
                        <x-fas-triangle-exclamation class="icon" aria-hidden="true" />
                        <div>
                            <strong>Community Reporting</strong>
                            <span>Report inappropriate or misleading content to keep the platform clean and trustworthy for everyone.</span>
                        </div>
                    </div>
                    <div class="aboutus-panel-item">
                        <x-fas-users class="icon" aria-hidden="true" />
                        <div>
                            <strong>Real User Ratings</strong>
                            <span>Every recommendation is backed by genuine ratings and reviews from the developer community.</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </header>

    {{-- Centered Metrics Section --}}
    <section class="aboutus-section-padding" id="metrics">
        <div class="aboutus-container">
            <div class="aboutus-section-head aboutus-scroll-reveal">
                <span class="aboutus-eyebrow">Core Metrics</span>
                <h2>Built for smarter web discovery</h2>
                <p>Our platform is designed to make finding useful development resources fast, easy, and community-driven. No more wasting hours searching — get trusted recommendations from real users in seconds.</p>
            </div>

            <div class="aboutus-metrics-grid">
                <article class="aboutus-metric-card aboutus-scroll-reveal">
                    <div class="aboutus-metric-icon">
                        <x-fas-magnifying-glass class="icon" aria-hidden="true" />
                    </div>
                    <h3>Discover</h3>
                    <p>Find the best websites, tools, and libraries recommended by real developers — all organized by categories and tags for easy browsing.</p>
                </article>

                <article class="aboutus-metric-card aboutus-scroll-reveal">
                    <div class="aboutus-metric-icon">
                        <x-far-star class="icon" aria-hidden="true" />
                    </div>
                    <h3>Review</h3>
                    <p>Rate and comment on resources you've tried. Share your honest experience to help other developers make informed decisions.</p>
                </article>

                <article class="aboutus-metric-card aboutus-scroll-reveal">
                    <div class="aboutus-metric-icon">
                        <x-fas-sliders class="icon" aria-hidden="true" />
                    </div>
                    <h3>Customize</h3>
                    <p>Personalize your experience with custom tags, themes, and fonts. Save posts for later and tailor your feed to match your interests.</p>
                </article>

                <article class="aboutus-metric-card aboutus-scroll-reveal">
                    <div class="aboutus-metric-icon">
                        <x-fas-flag class="icon" aria-hidden="true" />
                    </div>
                    <h3>Report</h3>
                    <p>Flag inappropriate or misleading content to help maintain a trustworthy community. Admins review reports to keep the platform reliable for everyone.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- Story Section --}}
    <section class="aboutus-section-padding" id="story">
        <div class="aboutus-story-container">
            <div class="aboutus-story-wrap">

                {{-- Left Narrative Box --}}
                <article class="aboutus-story-card aboutus-scroll-reveal">
                    <span class="aboutus-eyebrow">
                        <x-fas-feather-pointed class="icon" aria-hidden="true" style="width:0.72rem;height:0.72rem;" />
                        Our Story & Mission
                    </span>
                    <h2>Built to simplify resource discovery.</h2>
                    <p>SiteSphere was born out of a simple frustration — finding the right tools and resources as a developer takes too long. You end up browsing through dozens of documentation pages, watching tutorial videos, and testing multiple websites before landing on something that actually works. Good recommendations are scattered across the internet, buried under outdated blog posts and sponsored content.</p>
                    <br>
                    <p>To solve this, we built a single platform where the developer community can share, rate, and discuss the best resources they've found. Every recommendation comes from real users with real experience. By organizing everything into categories and tags with honest community ratings, we make sure you spend less time searching and more time <span class="aboutus-highlight">building with the right tools.</span></p>
                </article>

                {{-- Right Feature Stack --}}
                <div class="aboutus-story-points">

                    <div class="aboutus-story-point aboutus-scroll-reveal">
                        <div class="aboutus-story-point-header">
                            <div class="aboutus-story-icon-badge">
                                <x-fas-layer-group class="icon" aria-hidden="true" />
                            </div>
                            <h3>Organized by Category</h3>
                        </div>
                        <div class="aboutus-story-point-content">
                            <p>Every resource is organized into clear categories and tags, so you can browse by what you need — whether it's frontend frameworks, backend tools, design libraries, or deployment platforms.</p>
                            <ul class="aboutus-story-sub-details">
                                <li>Filter resources by category and custom tags.</li>
                                <li>Admin-managed categories for consistent organization.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="aboutus-story-point aboutus-scroll-reveal">
                        <div class="aboutus-story-point-header">
                            <div class="aboutus-story-icon-badge">
                                <x-far-star-half-stroke class="icon" aria-hidden="true" />
                            </div>
                            <h3>Honest Community Ratings</h3>
                        </div>
                        <div class="aboutus-story-point-content">
                            <p>Every resource gets rated by real users who have actually tried it. No paid placements, no sponsored rankings — just genuine feedback from the developer community to help you pick the right tool.</p>
                            <ul class="aboutus-story-sub-details">
                                <li>Star ratings and comments from verified users.</li>
                                <li>Report system to flag misleading or outdated content.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="aboutus-story-point aboutus-scroll-reveal">
                        <div class="aboutus-story-point-header">
                            <div class="aboutus-story-icon-badge">
                                <x-fas-bookmark class="icon" aria-hidden="true" />
                            </div>
                            <h3>Save & Revisit</h3>
                        </div>
                        <div class="aboutus-story-point-content">
                            <p>Bookmark the resources you find useful and build your own collection. With personalized tags and a saved posts library, you'll never lose track of a great tool again.</p>
                            <ul class="aboutus-story-sub-details">
                                <li>Save posts to your personal library for quick access.</li>
                                <li>Customize tags and themes to match your workflow.</li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    {{-- Team Carousel Section --}}
    <section class="aboutus-section-padding" id="team">
        <div class="aboutus-container aboutus-text-center">
            <div class="aboutus-section-head aboutus-scroll-reveal">
                <span class="aboutus-eyebrow">Meet Our Team</span>
                <h2>The people behind the project</h2>
                <p>Introducing our specialized development group who engineered and optimized the entire infrastructure behind the SiteSphere review platform.</p>
            </div>

            <div class="carousel-container scroll-reveal">
                <button class="carousel-btn prev-btn" aria-label="Previous team member">
                    <x-fas-chevron-left class="icon" aria-hidden="true" style="width:0.9rem;height:0.9rem;" />
                </button>
                <div class="carousel-track">
                    <div class="carousel-card">
                        <div class="member-avatar">
                            <img src="{{ asset('images/team/ant-htoo-aung.jpg') }}" alt="Ant Htoo Aung" loading="lazy" />
                        </div>
                        <h3>Ant Htoo Aung</h3>
                        <p class="role">Team Leader & Full-Stack Developer</p>
                        <p class="bio">Backend development, frontend compilation, and project architecture.</p>
                        <div class="tags"><span>Backend</span><span>Frontend</span><span>Architecture</span></div>
                        <div class="member-email"><a href="mailto:anthtooaung2792005@gmail.com">anthtooaung2792005@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-user-gear class="icon" aria-hidden="true" />
                        </div>
                        <h3>Hein Aung Kyaw</h3>
                        <p class="role">Co-Leader & Frontend Developer</p>
                        <p class="bio">Login/Register page, post detail page, logo and loading, and admin dashboard page.</p>
                        <div class="tags"><span>Login</span><span>Dashboard</span><span>Branding</span></div>
                        <div class="member-email"><a href="mailto:heinagkyaw123@gmail.com">heinagkyaw123@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <img src="{{ asset('images/team/eait-nadi-kyaw.jpg') }}" alt="Eaint Nadi Kyaw" loading="lazy" />
                        </div>
                        <h3>Eaint Nadi Kyaw</h3>
                        <p class="role">Co-Leader & Frontend Developer</p>
                        <p class="bio">Home page and admin users page.</p>
                        <div class="tags"><span>Home</span><span>Admin</span><span>Users</span></div>
                        <div class="member-email"><a href="mailto:eainteaint3359@gmail.com">eainteaint3359@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <img src="{{ asset('images/team/min-hein-ko.jpg') }}" alt="Min Hein Ko" loading="lazy" />
                        </div>
                        <h3>Min Hein Ko</h3>
                        <p class="role">Frontend Developer</p>
                        <p class="bio">Welcome page, report view page (admin), flow charts, and use-case diagram.</p>
                        <div class="tags"><span>Welcome</span><span>Reports</span><span>Diagrams</span></div>
                        <div class="member-email"><a href="mailto:minheinko58@gmail.com">minheinko58@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <img src="{{ asset('images/team/lin-thant-aung.jpg') }}" alt="Lin Thant Aung" loading="lazy" />
                        </div>
                        <h3>Lin Thant Aung</h3>
                        <p class="role">Frontend Developer</p>
                        <p class="bio">Profile settings page, appearance page, AG design, and security page.</p>
                        <div class="tags"><span>Profile</span><span>Appearance</span><span>Security</span></div>
                        <div class="member-email"><a href="mailto:linthantaung1210@gmail.com">linthantaung1210@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <img src="{{ asset('images/team/sa-kyaw-wai-yan-htet.jpg') }}" alt="Sa Kyaw Wai Yan Htet" loading="lazy" />
                        </div>
                        <h3>Sa Kyaw Wai Yan Htet</h3>
                        <p class="role">Frontend Developer</p>
                        <p class="bio">Saved post page, post card box section, and post upload page.</p>
                        <div class="tags"><span>Saved Posts</span><span>Cards</span><span>Upload</span></div>
                        <div class="member-email"><a href="mailto:aunglay306699@gmail.com">aunglay306699@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <img src="{{ asset('images/team/han-htoo-lwin.jpg') }}" alt="Han Htoo Lwin" loading="lazy" />
                        </div>
                        <h3>Han Htoo Lwin</h3>
                        <p class="role">Frontend Developer</p>
                        <p class="bio">Navigation section, welcome page footer, menu bar (hamburger), and report info section.</p>
                        <div class="tags"><span>Navigation</span><span>Footer</span><span>Menu</span></div>
                        <div class="member-email"><a href="mailto:hanhtoolwin69@gmail.com">hanhtoolwin69@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <img src="{{ asset('images/team/su-wati-myat-noe.jpg') }}" alt="Zune Myat Noe" loading="lazy" />
                        </div>
                        <h3>Zune Myat Noe</h3>
                        <p class="role">Frontend Developer</p>
                        <p class="bio">About us page, profile page, and profile show box.</p>
                        <div class="tags"><span>About</span><span>Profile</span><span>UI</span></div>
                        <div class="member-email"><a href="mailto:zunez5697@gmail.com">zunez5697@gmail.com</a></div>
                    </div>

                </div>

                <button class="carousel-btn next-btn" aria-label="Next team member">
                    <x-fas-chevron-right class="icon" aria-hidden="true" style="width:0.9rem;height:0.9rem;" />
                </button>
            </div>
        </div>
    </section>
</main>

<x-layout.footer class="mt-auto" />
@endsection