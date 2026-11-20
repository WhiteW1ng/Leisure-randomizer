<x-app-layout>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="mt-6 text-3xl font-bold">Каталог занятий</h1>
    <p class="mt-2 mb-6 text-ink-soft">Все одобренные занятия. Отметьте фильтры, чтобы сузить список.</p>

    <form method="GET" action="{{ route('catalog') }}" class="mb-8 rounded-lg border border-line bg-card p-5 shadow-sm">
        @include('partials.filter-panel')
        <div class="mt-5 flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-ink shadow hover:bg-accent-hover">Показать</button>
            @if ($hasFilters)
                <a href="{{ route('catalog') }}" class="text-sm text-ink-soft underline">Сбросить фильтры</a>
            @endif
        </div>
    </form>

    @if ($ideas->isEmpty())
        <p class="rounded-lg border border-warn-line bg-warn-bg p-4 text-warn-text">
            Ничего не найдено. Попробуйте убрать часть фильтров.
        </p>
    @else
        <p class="mb-4 text-sm text-mute">Найдено: {{ $ideas->total() }}</p>
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($ideas as $idea)
                @include('partials.idea-card', ['idea' => $idea, 'favoriteIds' => $favoriteIds])
            @endforeach
        </div>
        <div class="mt-8">{{ $ideas->links() }}</div>
    @endif
</div>
</x-app-layout>
