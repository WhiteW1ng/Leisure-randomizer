<x-public-layout>
    <h1 class="mt-6 text-3xl font-bold sm:text-4xl">Не знаете, чем заняться?</h1>
    <p class="mt-3 mb-8 max-w-2xl text-gray-600 dark:text-gray-300">
        Нажмите кнопку — и сервис предложит случайное занятие. Можно выбрать фильтры: сезон, компанию, бюджет, время и место.
    </p>

    @include('partials.random-idea', ['action' => route('home')])

    <div class="mt-12 flex flex-wrap items-center gap-4 border-t border-gray-300 pt-6 dark:border-gray-700">
        <a href="{{ route('catalog') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 font-medium hover:bg-white dark:border-gray-600 dark:hover:bg-gray-800">
            📚 Каталог занятий
        </a>
        <a href="{{ route('suggest.create') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-indigo-600 px-5 py-2.5 font-medium text-indigo-700 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-gray-800">
            ＋ Предложить идею
        </a>
    </div>
    @guest
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Предлагать идеи, сохранять избранное и вести историю могут только зарегистрированные пользователи: при нажатии мы перенаправим вас на вход.
        </p>
    @endguest
</x-public-layout>
