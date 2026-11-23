{{-- Блок «Случайное занятие». Нужны: $action, $options, $selected, $rolled, $idea, $useFilters, $favoriteIds --}}
<section class="w-full">
    <form id="random-form" method="GET" action="{{ $action }}">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Случайное занятие без фильтров --}}
            <button type="submit" name="mode" value="any"
                    class="inline-flex items-center gap-2 rounded-full bg-accent px-6 py-3 text-base font-semibold text-accent-ink shadow hover:bg-accent-hover focus:outline-none focus:ring-2 focus:ring-accent">
                🎲 Случайное занятие
            </button>

            {{-- Кнопка выбора фильтров: раскрывает панель ниже --}}
            <button type="button" id="filters-toggle" aria-expanded="{{ $useFilters ? 'true' : 'false' }}" aria-controls="filters-panel"
                    class="inline-flex items-center gap-2 rounded-full border border-line bg-card px-5 py-3 text-base font-medium text-ink shadow-sm hover:bg-chip">
                ⚙️ Фильтры
            </button>
        </div>

        <div id="filters-panel" class="mt-4 rounded-full border border-line bg-card p-5 shadow-sm {{ $useFilters ? '' : 'hidden' }}">
            @include('partials.filter-panel')

            <div class="mt-5">
                <button type="submit" name="mode" value="filters"
                        class="rounded-full bg-accent px-5 py-2.5 text-sm font-semibold text-accent-ink shadow hover:bg-accent-hover">
                    Подобрать с фильтрами
                </button>
                <span class="ml-3 text-xs text-mute">Если ничего не отметить, фильтр не применяется.</span>
            </div>
        </div>
    </form>

    @if ($rolled)
        <div class="mt-6">
            @if ($idea)
                @include('partials.idea-card', [
                    'idea' => $idea,
                    'favoriteIds' => $favoriteIds ?? [],
                    'reroll' => $useFilters ? 'filters' : 'any',
                ])
            @else
                <p class="rounded-full border border-warn-line bg-warn-bg p-4 text-warn-text">
                    Подходящих занятий не нашлось. Попробуйте убрать часть фильтров или нажмите «Случайное занятие».
                </p>
            @endif
        </div>
    @endif
</section>

{{-- Открытие и закрытие панели фильтров (без внешних библиотек) --}}
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
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
