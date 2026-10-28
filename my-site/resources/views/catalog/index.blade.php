<x-public-layout title="Каталог занятий">
    <h1 class="mt-6 text-3xl font-bold">Каталог занятий</h1>
    <p class="mt-2 mb-6 text-gray-600 dark:text-gray-300">Все одобренные занятия. Отметьте фильтры, чтобы сузить список.</p>

    <form method="GET" action="{{ route('catalog') }}" class="mb-8 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @include('partials.filter-panel')
        <div class="mt-5 flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">Показать</button>
            @if ($hasFilters)
                <a href="{{ route('catalog') }}" class="text-sm text-gray-600 underline dark:text-gray-300">Сбросить фильтры</a>
            @endif
        </div>
    </form>

    @if ($ideas->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800">
            Ничего не найдено. Попробуйте убрать часть фильтров.
        </p>
    @else
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Найдено: {{ $ideas->total() }}</p>
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($ideas as $idea)
                @include('partials.idea-card', ['idea' => $idea, 'favoriteIds' => $favoriteIds])
            @endforeach
        </div>
        <div class="mt-8">{{ $ideas->links() }}</div>
    @endif
</x-public-layout>
