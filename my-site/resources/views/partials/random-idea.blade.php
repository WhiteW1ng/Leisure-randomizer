{{-- Блок «Случайное занятие». Нужны: $action, $options, $selected, $rolled, $idea, $useFilters, $favoriteIds --}}
<section class="w-full">
    <form id="random-form" method="GET" action="{{ $action }}">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Случайное занятие без фильтров --}}
            <button type="submit" name="mode" value="any"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                🎲 Случайное занятие
            </button>

            {{-- Кнопка выбора фильтров: раскрывает панель ниже --}}
            <button type="button" id="filters-toggle" aria-expanded="{{ $useFilters ? 'true' : 'false' }}" aria-controls="filters-panel"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3 text-base font-medium text-gray-800 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                ⚙️ Фильтры
            </button>
        </div>

        <div id="filters-panel" class="mt-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 {{ $useFilters ? '' : 'hidden' }}">
            @include('partials.filter-panel')

            <div class="mt-5">
                <button type="submit" name="mode" value="filters"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    Подобрать с фильтрами
                </button>
                <span class="ml-3 text-xs text-gray-500 dark:text-gray-400">Если ничего не отметить, фильтр не применяется.</span>
            </div>
        </div>
    </form>

    @if ($rolled)
        <div class="mt-6">
            @if ($idea)
                @include('partials.idea-card', [
                    'idea' => $idea,
                    'favoriteIds' => $favoriteIds ?? [],
                    'extra' => new \Illuminate\Support\HtmlString(
                        '<button type="submit" form="random-form" name="mode" value="'.($useFilters ? 'filters' : 'any').'" class="rounded-lg border border-indigo-300 bg-white px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50 dark:bg-gray-700 dark:text-indigo-200">Выбрать другое</button>'
                    ),
                ])
            @else
                <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800">
                    Подходящих занятий не нашлось. Попробуйте убрать часть фильтров или нажмите «Случайное занятие».
                </p>
            @endif
        </div>
    @endif
</section>

{{-- Открытие и закрытие панели фильтров (без внешних библиотек) --}}
<script>
    (function () {
        var btn = document.getElementById('filters-toggle');
        var panel = document.getElementById('filters-panel');
        if (!btn || !panel) { return; }
        btn.addEventListener('click', function () {
            var hidden = panel.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        });
    })();
</script>
