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
