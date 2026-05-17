<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @yield('title', 'SiteSphere')
    </title>
    <link rel="shortcut icon" href="" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('sweetalert2::index')

</head>
<body>
    <x-layout.nav />
    @yield('content')
<script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
</body>
</html>
