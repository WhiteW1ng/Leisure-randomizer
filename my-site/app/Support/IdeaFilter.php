<?php

namespace App\Support;

use App\Models\ActivityLevel;
use App\Models\Budget;
use App\Models\Company;
use App\Models\Duration;
use App\Models\LocationType;
use App\Models\Season;
use Illuminate\Database\Eloquent\Builder;

/**
 * Общие правила фильтров для главной, дашборда и каталога.
 */
class IdeaFilter
{
    // Ключ из формы => [связь в модели Idea, таблица фильтра, подпись]
    public const MAP = [
        'seasons'         => ['seasons', 'seasons', 'Сезон'],
        'companies'       => ['companies', 'companies', 'Компания'],
        'activity_levels' => ['activityLevels', 'activity_levels', 'Активность'],
        'budgets'         => ['budgets', 'budgets', 'Бюджет'],
        'durations'       => ['durations', 'durations', 'Время'],
        'location_types'  => ['locationTypes', 'location_types', 'Место'],
    ];

    /** Связи, которые нужно подгрузить, чтобы показать теги */
    public static function relations(): array
    {
        return array_column(self::MAP, 0);
    }

    /** Правила проверки того, что пришло из адресной строки */
    public static function rules(): array
    {
        $rules = [];
        foreach (array_keys(self::MAP) as $key) {
            $rules[$key] = ['nullable', 'array'];
            $rules[$key.'.*'] = ['string', 'max:100'];
        }

        return $rules;
    }

    /** Выбранные значения фильтров из проверенных данных */
    public static function selected(array $validated): array
    {
        $selected = [];
        foreach (array_keys(self::MAP) as $key) {
            $selected[$key] = array_values(array_filter($validated[$key] ?? []));
        }

        return $selected;
    }

    /** Применяет фильтры: внутри группы — «любое из», между группами — «и» */
    public static function apply(Builder $query, array $selected): Builder
    {
        foreach (self::MAP as $key => [$relation, $table]) {
            $names = $selected[$key] ?? [];
            if ($names === []) {
                continue;
            }
            if ($key === 'location_types') {
                $names[] = 'любой'; // идея «в любом месте» подходит под любое место
            }
            $query->whereHas($relation, fn ($q) => $q->whereIn($table.'.name', $names));
        }

        return $query;
    }

    /** Списки значений для галочек в панели фильтров */
    public static function options(): array
    {
        return [
            'seasons'         => ['Сезон', Season::pluck('name')],
            'companies'       => ['Компания', Company::pluck('name')],
            'activity_levels' => ['Активность', ActivityLevel::pluck('name')],
            'budgets'         => ['Бюджет', Budget::pluck('name')],
            'durations'       => ['Время', Duration::pluck('name')],
            'location_types'  => ['Место', LocationType::pluck('name')],
        ];
    }
}
