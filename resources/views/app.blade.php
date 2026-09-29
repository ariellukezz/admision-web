{{-- <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Admision 2023') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    
    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}?v={{ time() }}">
</head>
<body class="font-sans antialiased">
    @inertia

    <!-- Scripts -->
    <script src="{{ mix('/js/app.js') }}?v={{ time() }}"></script>
</body>
</html> --}}



<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Admision Unap') }}</title>

        {{-- Tema del modulo Revisor: se fija antes de pintar para evitar el parpadeo --}}
        <script>
            (function () {
                try {
                    var g = localStorage.getItem('rev-theme');
                    var d = g ? g === 'dark'
                              : window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.setAttribute('data-theme', d ? 'dark' : 'light');
                } catch (e) {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,500;0,600;1,400&family=Instrument+Serif:ital@0;1&family=IBM+Plex+Mono:wght@400;500&display=swap">
        
        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
