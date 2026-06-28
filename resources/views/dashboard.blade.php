<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>
        @yield('title', 'SiteSphere')
    </title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <x-google-fonts />
    <script>
        window.toastPosition = {{ Illuminate\Support\Js::from($toastPosition ?? 'top-end') }};
    </script>
    @php($resolvedFontFamily = $fontFamily ?: 'Figtree, sans-serif')
    @auth
        <style>
            :root {
                --accent-color: {{ $themeColors['accent'] ?? '#6c5ce7' }};
                --background-color: {{ $themeColors['background'] ?? '#ffffff' }};
                --text-color: {{ $themeColors['text'] ?? '#0d1b2a' }};
                --font-family: {!! $resolvedFontFamily !!};
            }
        </style>
    @endauth
    @guest

        <style>
            :root{
                --accent-color: #6c5ce7;
                --background-color: #ffffff;
                --text-color: #0d1b2a;
                --font-family: {!! $resolvedFontFamily !!};
            }
        </style>
    @endguest
    <style>
        @media (min-width: 901px) {
            html, body {
                height: 100%;
                overflow: hidden;
            }
        }
        @media (max-width: 900px) {
            body {
                overflow: visible;
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('sweetalert2::index')
    @stack('styles')

</head>
<body class="m-0 box-border overflow-hidden p-0 bg-[var(--background-color)] text-[var(--text-color)]" style="font-family: var(--font-family);">
    <x-loading />
    @yield('content')
    @stack('scripts')
</body>
</html>
