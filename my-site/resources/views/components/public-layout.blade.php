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
                    <a href="{{ url('/profile') }}" class="rounded-md px-3 py-1.5 hover:underline">Личный кабинет</a>
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
                <div id="success-toast" class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300 flex justify-between items-center transition-opacity duration-500 ease-in-out">
                    <span class="font-medium">{{ session('success') }}</span>
                    <button onclick="hideToast()" class="ml-4 text-green-700 dark:text-green-300 hover:text-green-900 dark:hover:text-green-100 font-bold text-xl leading-none focus:outline-none" title="Закрыть">
                        &times;
                    </button>
                </div>

                <script>
                    function hideToast() {
                        const toast = document.getElementById('success-toast');
                        if (toast) {
                            toast.style.opacity = '0';
                            setTimeout(() => {
                                toast.style.display = 'none';
                            }, 500);
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(hideToast, 5000);
                    });
                </script>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
