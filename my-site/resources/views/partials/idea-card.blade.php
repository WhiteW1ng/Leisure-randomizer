{{-- Карточка занятия. Нужны: $idea; необязательно: $favoriteIds, $slot-кнопка через $extra --}}
<article class="rounded-xl border border-indigo-200 bg-indigo-50 p-6 shadow-sm dark:border-indigo-800 dark:bg-gray-800">
    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $idea->title }}</h2>
    @if ($idea->description)
        <p class="mt-3 text-gray-700 dark:text-gray-300">{{ $idea->description }}</p>
    @endif

    <div class="mt-4 flex flex-wrap gap-2 text-xs">
        @foreach (['locationTypes', 'budgets', 'durations', 'companies', 'activityLevels', 'seasons'] as $relation)
            @foreach ($idea->{$relation} as $tag)
                <span class="rounded-full bg-white px-3 py-1 text-gray-700 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600">{{ $tag->name }}</span>
            @endforeach
        @endforeach
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-3">
        @auth
            {{-- Избранное: кнопка-форма POST с CSRF --}}
            <form method="POST" action="{{ route('favorites.toggle', $idea) }}">
                @csrf
                <button type="submit"
                        class="rounded-lg border border-amber-300 bg-white px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50 dark:bg-gray-700 dark:text-amber-200">
                    {{ in_array($idea->id, $favoriteIds ?? [], true) ? '★ В избранном' : '☆ В избранное' }}
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="rounded-lg border border-amber-300 bg-white px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50 dark:bg-gray-700 dark:text-amber-200">
                ☆ В избранное (нужен вход)
            </a>
        @endauth

        {{ $extra ?? '' }}
    </div>
</article>
