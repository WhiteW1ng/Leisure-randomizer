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
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'seasons' => ['required', 'array', 'min:1', 'max:10'],
            'seasons.*' => ['string', 'distinct', Rule::exists('seasons', 'name')],
            'companies' => ['required', 'array', 'min:1', 'max:10'],
            'companies.*' => ['string', 'distinct', Rule::exists('companies', 'name')],
            'activity_levels' => ['required', 'array', 'min:1', 'max:10'],
            'activity_levels.*' => ['string', 'distinct', Rule::exists('activity_levels', 'name')],
            'budgets' => ['required', 'array', 'min:1', 'max:10'],
            'budgets.*' => ['string', 'distinct', Rule::exists('budgets', 'name')],
            'durations' => ['required', 'array', 'min:1', 'max:10'],
            'durations.*' => ['string', 'distinct', Rule::exists('durations', 'name')],
            'location_types' => ['required', 'array', 'min:1', 'max:10'],
            'location_types.*' => ['string', 'distinct', Rule::exists('location_types', 'name')],
        ]);

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
