@extends('index')
@section('title')
    SiteSphere
@endsection

@push('styles')
    @vite('resources/css/welcome.css')
    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            display: none !important;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        html, body {
            -ms-overflow-style: none !important;  /* IE and Edge */
            scrollbar-width: none !important;  /* Firefox */
        }
    </style>
@endpush

@push('scripts')
    @vite('resources/js/welcome.js')
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

    <main class="welcome-main">
        {{-- Welcome Hero Section --}}
        <section class="welcome-hero" id="hero" aria-labelledby="welcomeHeroTitle">
            <div class="welcome-hero-inner">
                <h1 id="welcomeHeroTitle" class="welcome-hero-title welcome-hero-reveal">
                    <span class="welcome-title-line">
                        <span class="welcome-swap-box welcome-word-toggle">
                            <span class="welcome-word welcome-accent-soft is-visible" data-word-state="a">Less</span>
                            <span class="welcome-word welcome-accent-soft" data-word-state="b">More</span>
                        </span>

                        <span class="welcome-static-text">Stack</span>

                        <span class="welcome-swap-box welcome-word-action">
                            <span class="welcome-word welcome-accent-soft is-visible" data-word-state="a">Searching</span>
                            <span class="welcome-word welcome-accent-soft" data-word-state="b">Building</span>
                        </span>
                    </span>
                </h1>

                <p class="welcome-hero-copy welcome-hero-reveal-delayed">
                    Don't drown in documentation. SiteSphere fast-tracks your development process by recommending the exact
                    libraries, tools, and platforms your project needs.
                </p>

                <form class="welcome-search welcome-hero-reveal-delayed" action="#" role="search" aria-label="Search trusted websites">
                    <label class="sr-only" for="welcomeSearch">Search for a trusted website</label>
                    <div class="welcome-search-bar">
                        <x-fas-search class="welcome-search-icon" aria-hidden="true" />
                        <input id="welcomeSearch" class="outline-none" type="search" placeholder="Search for a trusted website..." autocomplete="off">
                    </div>
                </form>
            </div>

            <a href="#reviews-section" class="welcome-scroll-down" data-welcome-scroll>
                <span>Explore</span>
                <x-fas-chevron-down class="welcome-scroll-icon" aria-hidden="true" />
            </a>
        </section>

        {{-- Welcome Trusted Websites Section--}}
        <section class="welcome-rated-section" id="reviews-section" aria-labelledby="reviewedWebsitesTitle">
            <h2 id="reviewedWebsitesTitle" class="welcome-section-title welcome-reveal">Most Reviewed Websites</h2>

            <div class="welcome-review-grid">
                @forelse ($mostReviewedPosts as $post)
                    @php
                        $averageRating = round((float) ($post->average_rating ?? 0), 1);
                        $filledStars = max(0, min(5, (int) round($averageRating)));
                        $trustScore = (int) round(($averageRating / 5) * 100);
                    @endphp

                    <article class="welcome-review-card welcome-reveal">
                        <div class="welcome-card-head">
                            <div class="welcome-name-box">
                                <h3>
                                    <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <span>{{ parse_url($post->url, PHP_URL_HOST) ?: $post->url }}</span>
                            </div>
                            <div class="welcome-stars" aria-label="{{ $averageRating }} out of 5 stars">
                                {{ str_repeat('★', $filledStars).str_repeat('☆', 5 - $filledStars) }}
                            </div>
                        </div>
                        <p class="welcome-card-desc">
                            Reviewed by {{ $post->visible_reviews_count }} {{ \Illuminate\Support\Str::plural('member', $post->visible_reviews_count) }} with an average rating of {{ number_format($averageRating, 1) }}.
                        </p>
                        <div class="welcome-score-wrap">
                            <div class="welcome-score-info"><span>Trust Score</span><span>{{ $trustScore }}%</span></div>
                            <div class="welcome-score-bg">
                                <div class="welcome-score-fill" data-width="{{ $trustScore }}%"></div>
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="welcome-review-card welcome-reveal">
                        <div class="welcome-card-head">
                            <div class="welcome-name-box">
                                <h3>No reviewed websites yet</h3>
                                <span>Be the first to share one.</span>
                            </div>
                            <div class="welcome-stars" aria-label="0 out of 5 stars">☆☆☆☆☆</div>
                        </div>
                        <p class="welcome-card-desc">
                            Real reviewed websites will appear here as soon as members publish visible reviews.
                        </p>
                    </article>
                @endforelse
            </div>

            <div class="welcome-see-more">
                <a href="{{ route('home') }}" class="welcome-outline-button">
                    <span>See More!</span>
                </a>
            </div>
        </section>

        {{-- Welcome Connect Section--}}
        <section class="welcome-connect-section" id="welcome-connect-section" aria-labelledby="contactTitle">
            <div class="welcome-connect-container">
                <div class="welcome-contact-form welcome-reveal welcome-reveal-1">
                    <form action="{{ route('contact.store') }}" method="POST" aria-label="Contact form">
                        @csrf
                        <h2 id="contactTitle" class="welcome-form-title">Get in touch</h2>

                        <div class="welcome-form-row">
                            <div class="welcome-form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required>
                                @if(isset($errors) && $errors->has('first_name'))
                                    <p class="welcome-form-error">{{ $errors->first('first_name') }}</p>
                                @endif
                            </div>
                            <div class="welcome-form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required>
                                @if(isset($errors) && $errors->has('last_name'))
                                    <p class="welcome-form-error">{{ $errors->first('last_name') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="welcome-form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                            @if(isset($errors) && $errors->has('email'))
                                <p class="welcome-form-error">{{ $errors->first('email') }}</p>
                            @endif
                        </div>

                        <div class="welcome-form-group">
                            <label for="message">What do you have in mind</label>
                            <textarea id="message" name="message" placeholder="Please enter query..." required>{{ old('message') }}</textarea>
                            @if(isset($errors) && $errors->has('message'))
                                <p class="welcome-form-error">{{ $errors->first('message') }}</p>
                            @endif
                        </div>

                        <button type="submit" class="welcome-submit-button">Submit</button>
                    </form>
                </div>

                <div class="welcome-vertical-divider" aria-hidden="true"></div>

                <div class="welcome-contact-info welcome-reveal welcome-reveal-2">
                    <h2>Contact us</h2>
                    <p>
                        Have a platform you'd like us to review or need help with our data? Reach out and our team will get
                        back to you shortly.
                    </p>

                    <a class="welcome-info-item" href="mailto:anthtooaung2792005@outlook.com">
                        <x-fas-envelope class="welcome-info-icon" aria-hidden="true" />
                        <span>anthtooaung2792005@outlook.com</span>
                    </a>

                    <a class="welcome-info-item" href="https://github.com/anthtooaung" target="_blank" rel="noopener noreferrer">
                        <x-fab-github class="welcome-info-icon" aria-hidden="true" />
                        <span>github.com/anthtooaung</span>
                    </a>

                    <a class="welcome-info-item" href="https://www.linkedin.com/in/ant-htoo-aung-460006395" target="_blank" rel="noopener noreferrer">
                        <x-fab-linkedin-in class="welcome-info-icon" aria-hidden="true" />
                        <span>linkedin.com/in/ant-htoo-aung-460006395</span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <x-layout.footer class="mt-auto"/>

@endsection
