@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name', 'Рандомайзер досуга') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-100 text-gray-900 antialiased dark:bg-gray-900 dark:text-gray-100">
        <header class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-3 px-6 py-5">
            <a href="{{ route('home') }}" class="text-lg font-bold">🎲 Рандомайзер досуга</a>
            <nav class="flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route('catalog') }}" class="rounded-md px-3 py-1.5 hover:underline">Каталог</a>
                @auth
                    <a href="{{ route('favorites.index') }}" class="rounded-md px-3 py-1.5 hover:underline">Избранное</a>
                    <a href="{{ route('history.index') }}" class="rounded-md px-3 py-1.5 hover:underline">История</a>
                    <a href="{{ route('dashboard') }}" class="rounded-md border border-gray-300 px-4 py-1.5 hover:bg-white dark:border-gray-600 dark:hover:bg-gray-800">Личный кабинет</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-1.5 hover:underline">Войти</a>
                    <a href="{{ route('register') }}" class="rounded-md border border-gray-300 px-4 py-1.5 hover:bg-white dark:border-gray-600 dark:hover:bg-gray-800">Регистрация</a>
                @endauth
            </nav>
        </header>

        <main class="mx-auto max-w-4xl px-6 pb-16">
            @if (session('error'))
                <p class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">{{ session('error') }}</p>
            @endif
            @if (session('success'))
                <p class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">{{ session('success') }}</p>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
