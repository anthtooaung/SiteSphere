<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @yield('title', 'SiteSphere')
    </title>
    <link rel="shortcut icon" href="" type="image/x-icon">
    @if(!empty($themeColors))
        <style>
            :root {
                --accent-color: {{ $themeColors['accent'] ?? '#6c5ce7' }};
                --background-color: {{ $themeColors['background'] ?? '#0d1b2a' }};
                --text-color: {{ $themeColors['text'] ?? '#ffffff' }};
            }
        </style>
    @else
        <style>
            :root{
                --accent-color: #6c5ce7;
                --background-color: #fffff;
                --text-color: #0d1b2a;
            }
        </style>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('sweetalert2::index')

</head>
<body class="m-0 box-border p-0 bg-[var(--background-color)] text-[var(--text-color)]">
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
</body>
</html>
