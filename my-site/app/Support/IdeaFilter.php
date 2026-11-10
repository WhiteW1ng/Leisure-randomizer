<?php

namespace App\Support;

use App\Models\ActivityLevel;
use App\Models\Budget;
use App\Models\Company;
use App\Models\Duration;
use App\Models\LocationType;
use App\Models\Season;
use Illuminate\Database\Eloquent\Builder;


class IdeaFilter
{

    public const MAP = [
        'seasons'         => ['seasons', 'seasons', 'Сезон'],
        'companies'       => ['companies', 'companies', 'Компания'],
        'activity_levels' => ['activityLevels', 'activity_levels', 'Активность'],
        'budgets'         => ['budgets', 'budgets', 'Бюджет'],
        'durations'       => ['durations', 'durations', 'Время'],
        'location_types'  => ['locationTypes', 'location_types', 'Место'],
    ];

    public static function relations(): array
    {
        return array_column(self::MAP, 0);
    }

    public static function rules(): array
    {
        $rules = [];
        foreach (array_keys(self::MAP) as $key) {
            $rules[$key] = ['nullable', 'array'];
            $rules[$key.'.*'] = ['string', 'max:100'];
        }

        return $rules;
    }

    public static function selected(array $validated): array
    {
        $selected = [];
        foreach (array_keys(self::MAP) as $key) {
            $selected[$key] = array_values(array_filter($validated[$key] ?? []));
        }

        return $selected;
    }

    public static function apply(Builder $query, array $selected): Builder
    {
        foreach (self::MAP as $key => [$relation, $table]) {
            $names = $selected[$key] ?? [];
            if ($names === []) {
                continue;
            }
            if ($key === 'location_types') {
                $names[] = 'любой'; 
            }
            $query->whereHas($relation, fn ($q) => $q->whereIn($table.'.name', $names));
        }

        return $query;
    }

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
