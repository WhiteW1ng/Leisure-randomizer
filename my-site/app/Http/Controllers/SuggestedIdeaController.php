<?php

namespace App\Http\Controllers;

use App\Models\ActivityLevel;
use App\Models\Budget;
use App\Models\Company;
use App\Models\Duration;
use App\Models\LocationType;
use App\Models\Season;
use App\Models\SuggestedIdea;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuggestedIdeaController extends Controller
{
    // Показывает форму и передаёт в неё все фильтры
    public function create()
    {
        $seasons = Season::all();
        $companies = Company::all();
        $activityLevels = ActivityLevel::all();
        $budgets = Budget::all();
        $durations = Duration::all();
        $locationTypes = LocationType::all();

        return view('suggest.create', compact(
            'seasons', 'companies', 'activityLevels',
            'budgets', 'durations', 'locationTypes'
        ));
    }

    // Сохраняет идею в базу
    public function store(Request $request)
    {
        // 1. Валидация (белый список полей). Значения фильтров должны существовать в справочниках.
        $rules = [
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
        $messages = [
            'title.required' => 'Введите название идеи.',
            'title.min' => 'Название должно быть не короче :min символов.',
            'title.max' => 'Название не должно быть длиннее :max символов.',
            'title.string' => 'Название должно быть текстом.',
            'description.max' => 'Описание не должно быть длиннее :max символов.',
            'description.string' => 'Описание должно быть текстом.',
        ];

        // Шесть групп фильтров: таблица справочника => подпись поля
        $groups = [
            'seasons' => 'Сезон',
            'companies' => 'Компания',
            'activity_levels' => 'Активность',
            'budgets' => 'Бюджет',
            'durations' => 'Время',
            'location_types' => 'Место',
        ];
        foreach ($groups as $key => $label) {
            $rules[$key] = ['required', 'array', 'min:1', 'max:10'];
            $rules[$key.'.*'] = ['string', 'max:100', 'distinct', Rule::exists($key, 'name')];

            $messages[$key.'.required'] = "Выберите хотя бы одно значение в поле «{$label}».";
            $messages[$key.'.min'] = "Выберите хотя бы одно значение в поле «{$label}».";
            $messages[$key.'.max'] = "В поле «{$label}» можно выбрать не больше :max значений.";
            $messages[$key.'.array'] = "Поле «{$label}» заполнено неверно.";
            $messages[$key.'.*.string'] = "Поле «{$label}» заполнено неверно.";
            $messages[$key.'.*.max'] = "Поле «{$label}» заполнено неверно.";
            $messages[$key.'.*.distinct'] = "Значения в поле «{$label}» повторяются.";
            $messages[$key.'.*.exists'] = "Выбранного значения нет в списке поля «{$label}».";
        }

        // validate() берёт только поля из $rules: лишние (status, user_id, role) в $validated не попадут
        $validated = $request->validate($rules, $messages);

        // 2. Служебные поля берём только с сервера, не из запроса
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';
        $validated['created_at'] = now();

        // 3. Сохраняем в базу
        SuggestedIdea::create($validated);

        // 4. Возвращаем пользователя обратно с сообщением об успехе
        return redirect()->route('home')->with('success', 'Спасибо! Твоя идея отправлена на рассмотрение.');
    }
}
