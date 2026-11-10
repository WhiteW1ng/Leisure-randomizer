<x-app-layout>
    {{-- Верхний блок --}}
    <section class="bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-28 sm:pt-20 sm:pb-32">
            <p class="inline-block rounded-full bg-white/20 px-4 py-1 text-sm font-medium">🎲 Рандомайзер досуга</p>
            <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight">Не знаете, чем заняться?</h1>
            <p class="mt-4 max-w-2xl text-lg text-indigo-100">
                Нажмите кнопку, и мы предложим случайное занятие. Хотите точнее? Отметьте фильтры:
                сезон, компанию, бюджет, время и место.
            </p>
            <p class="mt-6 text-sm text-indigo-100">Занятий в каталоге: <span class="font-semibold text-white">{{ $total }}</span></p>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 pb-16">
        {{-- Сообщения: успех (после отправки идеи) и ошибка (например, нет доступа) --}}
        @if (session('success'))
            <p id="flash-success" class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 shadow-sm">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm">{{ session('error') }}</p>
        @endif

        {{-- Главный блок: кнопки, фильтры, результат --}}
        <div class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5 dark:bg-gray-800 sm:p-8">
            @include('partials.random-idea', ['action' => route('home')])
        </div>

        {{-- Ссылки на другие страницы --}}
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('catalog') }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800 dark:ring-gray-700">
                <div class="text-2xl">📚</div>
                <h2 class="mt-2 font-semibold text-gray-900 dark:text-gray-100">Каталог</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Все занятия списком, с фильтрами.</p>
            </a>

            <a href="{{ route('suggest.create') }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800 dark:ring-gray-700">
                <div class="text-2xl">💡</div>
                <h2 class="mt-2 font-semibold text-gray-900 dark:text-gray-100">Предложить идею</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    @auth
                        Ваша идея появится на сайте после проверки.
                    @else
                        Нужен вход: мы перенаправим вас на страницу входа.
                    @endauth
                </p>
            </a>

            @auth
                <a href="{{ route('favorites.index') }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-2xl">⭐</div>
                    <h2 class="mt-2 font-semibold text-gray-900 dark:text-gray-100">Избранное</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Занятия, которые вы сохранили.</p>
                </a>

                <a href="{{ route('history.index') }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-2xl">🕘</div>
                    <h2 class="mt-2 font-semibold text-gray-900 dark:text-gray-100">История</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Что выпало вам раньше.</p>
                </a>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.suggested-ideas.index') }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800">
                        <div class="text-2xl">🛡️</div>
                        <h2 class="mt-2 font-semibold text-gray-900 dark:text-gray-100">Модерация</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Одобрить или отклонить предложенные идеи.</p>
                    </a>
                @endif
            @else
                <a href="{{ route('register') }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-2xl">👤</div>
                    <h2 class="mt-2 font-semibold text-gray-900 dark:text-gray-100">Регистрация</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Избранное, история и свои идеи.</p>
                </a>

                <a href="{{ route('login') }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-2xl">🔑</div>
                    <h2 class="mt-2 font-semibold text-gray-900 dark:text-gray-100">Войти</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Уже есть аккаунт? Заходите.</p>
                </a>
            @endauth
        </div>
    </div>
</x-app-layout>
