@extends('index')
@section('title')
    About Us | SiteSphere
@endsection

@push('styles')
    @vite('resources/css/about-us.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            --font-family: Figtree, sans-serif;
        }
    </style>
@overwrite

@section('content')
    <x-layout.nav />
    <div class="md:mt-20"></div>

    <main class="aboutus-main">
        {{-- Hero Section --}}
        <header class="aboutus-hero" id="top">
            <div class="aboutus-container aboutus-hero-grid">
                <div class="aboutus-hero-content aboutus-scroll-reveal">
                    <span class="aboutus-eyebrow">
                        <x-fas-shield-halved class="icon" aria-hidden="true" style="width:0.72rem;height:0.72rem;" />
                        About SiteSphere
                    </span>
                    <h1>Browse the web safely, <span>without the noise.</span></h1>

                    <div class="aboutus-hero-body-content">
                        <p class="aboutus-hero-copy">SiteSphere is an uncompromised website review platform built for modern internet users and creators. We eliminate digital exhaustion by exposing dangerous scams, filtering out useless clutter, and tracking genuine, high-utility websites within a single transparent tracking ecosystem.</p>
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
                            <x-fas-s class="icon" aria-hidden="true" style="width:1.6rem;height:1.6rem;" />
                        </div>
                        <h4>Platform Standards</h4>
                    </div>
                    <div class="aboutus-panel-list">
                        <div class="aboutus-panel-item">
                            <x-fas-filter class="icon" aria-hidden="true" />
                            <div>
                                <strong>Proactive Clutter Filtering</strong>
                                <span>Isolate high-performing, useful web products instantly while dodging completely dead-end websites.</span>
                            </div>
                        </div>
                        <div class="aboutus-panel-item">
                            <x-fas-triangle-exclamation class="icon" aria-hidden="true" />
                            <div>
                                <strong>Anti-Scam Surveillance</strong>
                                <span>Every tracked web domain undergoes direct monitoring for deceptive billing traps or phishing attempts.</span>
                            </div>
                        </div>
                        <div class="aboutus-panel-item">
                            <x-fas-users class="icon" aria-hidden="true" />
                            <div>
                                <strong>Uncompromised Community Trust</strong>
                                <span>Leverage crowd-sourced ratings and real-time reports away from paid marketing bias.</span>
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
                    <h2>Engineered for reliable web navigation</h2>
                    <p>Our operational framework focuses on rendering website discovery rapid, accurate, and completely secure. We maximize browsing efficiency by targeting the exact malicious scams and low-quality digital clutter that waste your time.</p>
                </div>

                <div class="aboutus-metrics-grid">
                    <article class="aboutus-metric-card aboutus-scroll-reveal">
                        <div class="aboutus-metric-icon">
                            <x-fas-magnifying-glass class="icon" aria-hidden="true" />
                        </div>
                        <h3>Discover</h3>
                        <p>Locate pinpoint accurate user experiences, high-utility tools, and obscure web applications verified to solve your day-to-day requirements quickly.</p>
                    </article>

                    <article class="aboutus-metric-card aboutus-scroll-reveal">
                        <div class="aboutus-metric-icon">
                            <x-far-star class="icon" aria-hidden="true" />
                        </div>
                        <h3>Review</h3>
                        <p>Access transparent, unmanipulated evaluations from verified platform users regarding operational safety before engaging with third-party sites.</p>
                    </article>

                    <article class="aboutus-metric-card aboutus-scroll-reveal">
                        <div class="aboutus-metric-icon">
                            <x-fas-sliders class="icon" aria-hidden="true" />
                        </div>
                        <h3>Customize</h3>
                        <p>Tailor your preferred layout views, adjust security warning parameters, and personalize your tracking feed to align perfectly with your exact digital ecosystem fields.</p>
                    </article>

                    <article class="aboutus-metric-card aboutus-scroll-reveal">
                        <div class="aboutus-metric-icon">
                            <x-fas-globe class="icon" aria-hidden="true" />
                        </div>
                        <h3>Expose Junk</h3>
                        <p>Analyze structural responsiveness, broken layout designs, and ad-heavy platforms to prevent navigating to low-quality web spaces entirely.</p>
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
                        <h2>Built to clean up the digital landscape.</h2>
                        <p>SiteSphere was born out of growing frustration with the modern web. While the internet holds millions of active platforms, finding an honest, functional web service has become an exhausting task. Users and development teams are constantly forced to filter through deceptive phishing traps, lookalike domains, and ad-heavy clickbait built purely to capture ad revenue. Sponsored listings and manipulated corporate ratings hide the truth, making discovery stressful.</p>
                        <br>
                        <p>To solve this bottleneck, we engineered a transparent tracking catalog that filters the noise at its source. We developed an independent database that applies rigorous evaluation criteria across every tier of the web. By mapping the dark corners of the scum web and grading code integrity, we make sure that everyday users and creators spend their time on tools and platforms that <span class="aboutus-highlight">actually work reliably.</span></p>
                    </article>

                    {{-- Right Feature Stack --}}
                    <div class="aboutus-story-points">

                        <div class="aboutus-story-point aboutus-scroll-reveal">
                            <div class="aboutus-story-point-header">
                                <div class="aboutus-story-icon-badge">
                                    <x-fas-skull-crossbones class="icon" aria-hidden="true" />
                                </div>
                                <h3>Exposing Scum Web</h3>
                            </div>
                            <div class="aboutus-story-point-content">
                                <p>We maintain an active threat repository that processes user inputs and layout behavior to isolate hidden billing traps, forced micro-transactions, and phishing forms before damage occurs.</p>
                                <ul class="aboutus-story-sub-details">
                                    <li>Real-time database updates for dynamic domain blacklists.</li>
                                    <li>Automated script checks to flag structural spoofing indicators.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="aboutus-story-point aboutus-scroll-reveal">
                            <div class="aboutus-story-point-header">
                                <div class="aboutus-story-icon-badge">
                                    <x-far-star-half-stroke class="icon" aria-hidden="true" />
                                </div>
                                <h3>Transparent Star Ratings</h3>
                            </div>
                            <div class="aboutus-story-point-content">
                                <p>Our multi-tier framework scoring engine dissects platforms completely. We separate true high-utility hubs from bloated, broken landing pages that only serve search engines rather than users.</p>
                                <ul class="aboutus-story-sub-details">
                                    <li>Granular metrics covering responsiveness and layout shifts.</li>
                                    <li>Verified user evaluation streams free from corporate placement.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="aboutus-story-point aboutus-scroll-reveal">
                            <div class="aboutus-story-point-header">
                                <div class="aboutus-story-icon-badge">
                                    <x-fas-compass class="icon" aria-hidden="true" />
                                </div>
                                <h3>Reliable Web Discovery</h3>
                            </div>
                            <div class="aboutus-story-point-content">
                                <p>We firmly reject paid advertisements or algorithmic manipulation. Every ranking matrix factor stems purely from standard system logs, loading speed, clean script runtimes, and community value tokens.</p>
                                <ul class="aboutus-story-sub-details">
                                    <li>Organic tracking indexing focused on genuine utility output.</li>
                                    <li>Advanced customization filters to adjust personal layout spaces.</li>
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

                <div class="aboutus-carousel-container aboutus-scroll-reveal">
                    <button class="aboutus-carousel-btn aboutus-prev-btn" aria-label="Previous team member">
                        <x-fas-chevron-left class="icon" aria-hidden="true" style="width:0.9rem;height:0.9rem;" />
                    </button>
                    <div class="aboutus-carousel-track">
                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-user-tie class="icon" aria-hidden="true" />
                            </div>
                            <h3>Ant Htoo Aung</h3>
                            <p class="aboutus-role">Team Leader & Backend Developer</p>
                            <p class="aboutus-bio">Handled backend integration, navigation, authentication systems, and connected frontend components with backend functionality.</p>
                            <div class="aboutus-tags"><span>Backend</span><span>Auth</span><span>Integration</span></div>
                            <div class="aboutus-member-email"><a href="mailto:anthtooaung2792005@gmail.com">anthtooaung2792005@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-user-gear class="icon" aria-hidden="true" />
                            </div>
                            <h3>Hein Aung Kyaw</h3>
                            <p class="aboutus-role">Co-Leader & Full-Stack Developer</p>
                            <p class="aboutus-bio">Created authentication, security features, admin dashboard, post detail page, logo, and loading animations.</p>
                            <div class="aboutus-tags"><span>Security</span><span>Dashboard</span><span>Branding</span></div>
                            <div class="aboutus-member-email"><a href="mailto:heinagkyaw123@gmail.com">heinagkyaw123@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-user-pen class="icon" aria-hidden="true" />
                            </div>
                            <h3>Eaint Nadi Kyaw</h3>
                            <p class="aboutus-role">Co-Leader & Full-Stack Developer</p>
                            <p class="aboutus-bio">Developed the home page, admin users page, and user detail views for smooth user management.</p>
                            <div class="aboutus-tags"><span>Home UI</span><span>Admin</span><span>User View</span></div>
                            <div class="aboutus-member-email"><a href="mailto:eainteaint3359@gmail.com">eainteaint3359@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-user-check class="icon" aria-hidden="true" />
                            </div>
                            <h3>Min Hein Ko</h3>
                            <p class="aboutus-role">Frontend & Testing Developer</p>
                            <p class="aboutus-bio">Built welcome and admin report view pages to support first impressions and organized reporting.</p>
                            <div class="aboutus-tags"><span>Welcome</span><span>Reports</span><span>Testing</span></div>
                            <div class="aboutus-member-email"><a href="mailto:minheinko58@gmail.com">minheinko58@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-palette class="icon" aria-hidden="true" />
                            </div>
                            <h3>Lin Thant Aung</h3>
                            <p class="aboutus-role">Frontend & UI Tester</p>
                            <p class="aboutus-bio">Worked on profile settings, appearance page, and tag design system for better personalization.</p>
                            <div class="aboutus-tags"><span>Profile</span><span>Appearance</span><span>Tags</span></div>
                            <div class="aboutus-member-email"><a href="mailto:linthantaung1210@gmail.com">linthantaung1210@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-bookmark class="icon" aria-hidden="true" />
                            </div>
                            <h3>Sa Kyaw Wai Yan Htet</h3>
                            <p class="aboutus-role">Frontend & Data Manager</p>
                            <p class="aboutus-bio">Implemented the saved post feature so users can store and revisit important resources easily.</p>
                            <div class="aboutus-tags"><span>Saved Posts</span><span>Data</span><span>Frontend</span></div>
                            <div class="aboutus-member-email"><a href="mailto:aunglay306699@gmail.com">aunglay306699@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-flag class="icon" aria-hidden="true" />
                            </div>
                            <h3>Han Htoo Lwin</h3>
                            <p class="aboutus-role">Frontend & Report Tester</p>
                            <p class="aboutus-bio">Improved footer welcome and report information sections for better structure and communication.</p>
                            <div class="aboutus-tags"><span>Footer</span><span>Reports</span><span>UX</span></div>
                            <div class="aboutus-member-email"><a href="mailto:hanhtoolwin69@gmail.com">hanhtoolwin69@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-id-card class="icon" aria-hidden="true" />
                            </div>
                            <h3>Zune Myat Noe</h3>
                            <p class="aboutus-role">Profile & Frontend Developer</p>
                            <p class="aboutus-bio">Developed the about page, profile page, and profile show box for a more engaging experience.</p>
                            <div class="aboutus-tags"><span>About</span><span>Profile</span><span>Frontend</span></div>
                            <div class="aboutus-member-email"><a href="mailto:zunez5697@gmail.com">zunez5697@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-upload class="icon" aria-hidden="true" />
                            </div>
                            <h3>Zin Wai Yan Htet</h3>
                            <p class="aboutus-role">Upload & Testing Developer</p>
                            <p class="aboutus-bio">Created post upload, post card box, and hamburger menu sections for smoother content management.</p>
                            <div class="aboutus-tags"><span>Upload</span><span>Cards</span><span>Mobile Nav</span></div>
                            <div class="aboutus-member-email"><a href="mailto:zinhtetyan69@gmail.com">zinhtetyan69@gmail.com</a></div>
                        </div>

                        <div class="aboutus-carousel-card">
                            <div class="aboutus-member-avatar">
                                <x-fas-clipboard-check class="icon" aria-hidden="true" />
                            </div>
                            <h3>Thaw Tar Ko</h3>
                            <p class="aboutus-role">Testing & Daily Report Manager</p>
                            <p class="aboutus-bio">Handled system testing, bug checking, and daily progress reports to keep the project organized.</p>
                            <div class="aboutus-tags"><span>Testing</span><span>Reports</span><span>Quality</span></div>
                            <div class="aboutus-member-email"><a href="mailto:dizzykitty910@gmail.com">dizzykitty910@gmail.com</a></div>
                        </div>
                    </div>

                    <button class="aboutus-carousel-btn aboutus-next-btn" aria-label="Next team member">
                        <x-fas-chevron-right class="icon" aria-hidden="true" style="width:0.9rem;height:0.9rem;" />
                    </button>
                </div>
            </div>
        </section>
    </main>

    <x-layout.footer class="mt-auto" />
@endsection
