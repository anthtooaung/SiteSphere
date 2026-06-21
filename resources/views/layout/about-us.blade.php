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
                            <x-fas-user-tie class="icon" aria-hidden="true" />
                        </div>
                        <h3>Ant Htoo Aung</h3>
                        <p class="role">Team Leader & Backend Developer</p>
                        <p class="bio">Handled backend integration, navigation, authentication systems, and connected frontend components with backend functionality.</p>
                        <div class="tags"><span>Backend</span><span>Auth</span><span>Integration</span></div>
                        <div class="member-email"><a href="mailto:anthtooaung2792005@gmail.com">anthtooaung2792005@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-user-gear class="icon" aria-hidden="true" />
                        </div>
                        <h3>Hein Aung Kyaw</h3>
                        <p class="role">Co-Leader & Full-Stack Developer</p>
                        <p class="bio">Created authentication, security features, admin dashboard, post detail page, logo, and loading animations.</p>
                        <div class="tags"><span>Security</span><span>Dashboard</span><span>Branding</span></div>
                        <div class="member-email"><a href="mailto:heinagkyaw123@gmail.com">heinagkyaw123@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-user-pen class="icon" aria-hidden="true" />
                        </div>
                        <h3>Eaint Nadi Kyaw</h3>
                        <p class="role">Co-Leader & Full-Stack Developer</p>
                        <p class="bio">Developed the home page, admin users page, and user detail views for smooth user management.</p>
                        <div class="tags"><span>Home UI</span><span>Admin</span><span>User View</span></div>
                        <div class="member-email"><a href="mailto:eainteaint3359@gmail.com">eainteaint3359@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-user-check class="icon" aria-hidden="true" />
                        </div>
                        <h3>Min Hein Ko</h3>
                        <p class="role">Frontend & Testing Developer</p>
                        <p class="bio">Built welcome and admin report view pages to support first impressions and organized reporting.</p>
                        <div class="tags"><span>Welcome</span><span>Reports</span><span>Testing</span></div>
                        <div class="member-email"><a href="mailto:minheinko58@gmail.com">minheinko58@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-palette class="icon" aria-hidden="true" />
                        </div>
                        <h3>Lin Thant Aung</h3>
                        <p class="role">Frontend & UI Tester</p>
                        <p class="bio">Worked on profile settings, appearance page, and tag design system for better personalization.</p>
                        <div class="tags"><span>Profile</span><span>Appearance</span><span>Tags</span></div>
                        <div class="member-email"><a href="mailto:linthantaung1210@gmail.com">linthantaung1210@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-bookmark class="icon" aria-hidden="true" />
                        </div>
                        <h3>Sa Kyaw Wai Yan Htet</h3>
                        <p class="role">Frontend & Data Manager</p>
                        <p class="bio">Implemented the saved post feature so users can store and revisit important resources easily.</p>
                        <div class="tags"><span>Saved Posts</span><span>Data</span><span>Frontend</span></div>
                        <div class="member-email"><a href="mailto:aunglay306699@gmail.com">aunglay306699@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-flag class="icon" aria-hidden="true" />
                        </div>
                        <h3>Han Htoo Lwin</h3>
                        <p class="role">Frontend & Report Tester</p>
                        <p class="bio">Improved footer welcome and report information sections for better structure and communication.</p>
                        <div class="tags"><span>Footer</span><span>Reports</span><span>UX</span></div>
                        <div class="member-email"><a href="mailto:hanhtoolwin69@gmail.com">hanhtoolwin69@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-id-card class="icon" aria-hidden="true" />
                        </div>
                        <h3>Zune Myat Noe</h3>
                        <p class="role">Profile & Frontend Developer</p>
                        <p class="bio">Developed the about page, profile page, and profile show box for a more engaging experience.</p>
                        <div class="tags"><span>About</span><span>Profile</span><span>Frontend</span></div>
                        <div class="member-email"><a href="mailto:zunez5697@gmail.com">zunez5697@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-upload class="icon" aria-hidden="true" />
                        </div>
                        <h3>Zin Wai Yan Htet</h3>
                        <p class="role">Upload & Testing Developer</p>
                        <p class="bio">Created post upload, post card box, and hamburger menu sections for smoother content management.</p>
                        <div class="tags"><span>Upload</span><span>Cards</span><span>Mobile Nav</span></div>
                        <div class="member-email"><a href="mailto:zinhtetyan69@gmail.com">zinhtetyan69@gmail.com</a></div>
                    </div>

                    <div class="carousel-card">
                        <div class="member-avatar">
                            <x-fas-clipboard-check class="icon" aria-hidden="true" />
                        </div>
                        <h3>Thaw Tar Ko</h3>
                        <p class="role">Testing & Daily Report Manager</p>
                        <p class="bio">Handled system testing, bug checking, and daily progress reports to keep the project organized.</p>
                        <div class="tags"><span>Testing</span><span>Reports</span><span>Quality</span></div>
                        <div class="member-email"><a href="mailto:dizzykitty910@gmail.com">dizzykitty910@gmail.com</a></div>
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