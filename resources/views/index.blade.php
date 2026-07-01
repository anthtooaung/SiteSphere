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

   <!-- Primary Meta Tags -->
<title>
            SiteSphere
    </title>
<meta name="title" content="
            SiteSphere
    " />
<meta name="description" content="SiteSphere - A modern web community platform for sharing posts, connecting with others, and discovering great content." />

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website" />
<meta property="og:url" content="https://sitesphere-production.site/" />
<meta property="og:title" content="
            SiteSphere
    " />
<meta property="og:description" content="SiteSphere - A modern web community platform for sharing posts, connecting with others, and discovering great content." />
<meta property="og:image" content="https://github.com/anthtooaung/SiteSphere/blob/main/public/images/welcome.png" />

<!-- X (Twitter) -->
<meta property="twitter:card" content="summary_large_image" />
<meta property="twitter:url" content="https://sitesphere-production.site/" />
<meta property="twitter:title" content="
            SiteSphere
    " />
<meta property="twitter:description" content="SiteSphere - A modern web community platform for sharing posts, connecting with others, and discovering great content." />
<meta property="twitter:image" content="https://github.com/anthtooaung/SiteSphere/blob/main/public/images/welcome.png" />

<!-- Meta Tags Generated with https://metatags.io -->
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
