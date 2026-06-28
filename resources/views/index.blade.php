<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="8007Dn2OGySnWYZkb4rF7duLmUQKnhLV0lLPd83lvGg" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @yield('title', 'SiteSphere')
    </title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('description', 'SiteSphere - A modern web community platform for sharing posts, connecting with others, and discovering great content.')">
    <meta name="keywords" content="@yield('keywords', 'community, social, posts, blog, content sharing, discussion, forums')">
    <meta name="author" content="SiteSphere">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="@yield('canonical', 'https://sitesphere.xyz')">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('og_url', 'https://sitesphere.xyz')">
    <meta property="og:title" content="@yield('og_title', 'SiteSphere - Share & Connect')">
    <meta property="og:description" content="@yield('og_description', 'Join SiteSphere to share posts, connect with others, and discover great content.')">
    <meta property="og:image" content="@yield('og_image', 'https://sitesphere.xyz/og-image.png')">
    <meta property="og:site_name" content="SiteSphere">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', 'SiteSphere - Share & Connect')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Join SiteSphere to share posts, connect with others, and discover great content.')">
    <meta name="twitter:image" content="@yield('twitter_image', 'https://sitesphere.xyz/og-image.png')">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <x-google-fonts />
    <script>
        window.toastPosition = {{ Illuminate\Support\Js::from($toastPosition ?? 'top-end') }};
    </script>
    @php($resolvedFontFamily = $fontFamily ?: 'Figtree, sans-serif')
    @section('theme-style')
        <style>
            :root {
                --accent-color: {{ $themeColors['accent'] ?? '#6c5ce7' }};
                --background-color: {{ $themeColors['background'] ?? '#ffffff' }};
                --text-color: {{ $themeColors['text'] ?? '#0d1b2a' }};
                --font-family: {!! $resolvedFontFamily !!};
            }
        </style>
    @show
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('sweetalert2::index')
    @stack('styles')

</head>
<body class="m-0 box-border p-0 bg-[var(--background-color)] text-[var(--text-color)]" style="font-family: var(--font-family);">
    <x-loading />
    @yield('content')
    @stack('scripts')
</body>
</html>
