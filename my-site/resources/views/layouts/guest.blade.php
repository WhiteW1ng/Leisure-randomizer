<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
        @include('partials.theme-init')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-page text-ink">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-page">
            <div>
                <a href="/" class="flex items-center gap-2 font-serif text-2xl text-ink">
                    <span class="text-accent">✦</span> Досуг-Рандом
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-card shadow-md overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
        @include('partials.theme-toggle')
    </body>
</html>
