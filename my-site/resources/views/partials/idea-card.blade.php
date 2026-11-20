{{-- Карточка занятия. Нужны: $idea; необязательно: $favoriteIds, $slot-кнопка через $extra --}}
<article class="rounded-xl border border-accent bg-chip p-6 shadow-sm">
    <h2 class="text-xl font-bold text-ink">{{ $idea->title }}</h2>
    @if ($idea->description)
        <p class="mt-3 text-ink">{{ $idea->description }}</p>
    @endif

    <div class="mt-4 flex flex-wrap gap-2 text-xs">
        @foreach (['locationTypes', 'budgets', 'durations', 'companies', 'activityLevels', 'seasons'] as $relation)
            @foreach ($idea->{$relation} as $tag)
                <span class="rounded-full bg-card px-3 py-1 text-ink ring-1 ring-line">{{ $tag->name }}</span>
            @endforeach
        @endforeach
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-3">
        @auth
            {{-- Избранное: кнопка-форма POST с CSRF --}}
            <form method="POST" action="{{ route('favorites.toggle', $idea) }}">
                @csrf
                <button type="submit"
                        class="rounded-lg border border-accent bg-card px-4 py-2 text-sm font-medium text-accent-text hover:bg-warn-bg">
                    {{ in_array($idea->id, $favoriteIds ?? [], true) ? '★ В избранном' : '☆ В избранное' }}
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="rounded-lg border border-accent bg-card px-4 py-2 text-sm font-medium text-accent-text hover:bg-warn-bg">
                ☆ В избранное (нужен вход)
            </a>
        @endauth

        @if (!empty($reroll))
            {{-- $reroll задаётся в контроллере и равен только 'any' или 'filters' --}}
            <button type="submit" form="random-form" name="mode" value="{{ $reroll }}"
                    class="rounded-lg border border-accent bg-card px-4 py-2 text-sm font-medium text-accent-text hover:bg-chip">
                Выбрать другое
            </button>
        @endif
    </div>
</article>
