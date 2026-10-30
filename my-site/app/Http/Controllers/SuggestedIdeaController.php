<?php

namespace App\Http\Controllers;

use App\Models\SuggestedIdea;
use Illuminate\Http\Request;

class SuggestedIdeaController extends Controller
{
    // Показывает форму и передаёт в неё все фильтры
    public function create()
    {
        $seasons = \App\Models\Season::all();
        $companies = \App\Models\Company::all();
        $activityLevels = \App\Models\ActivityLevel::all();
        $budgets = \App\Models\Budget::all();
        $durations = \App\Models\Duration::all();
        $locationTypes = \App\Models\LocationType::all();

        return view('suggest.create', compact(
            'seasons', 'companies', 'activityLevels', 
            'budgets', 'durations', 'locationTypes'
        ));
    }

    // Сохраняет идею в базу
    public function store(Request $request)
    {
        // 1. Валидация
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'seasons' => 'required|array|min:1',
            'seasons.*' => 'string',
            'companies' => 'required|array|min:1',
            'companies.*' => 'string',
            'activity_levels' => 'required|array|min:1',
            'activity_levels.*' => 'string',
            'budgets' => 'required|array|min:1',
            'budgets.*' => 'string',
            'durations' => 'required|array|min:1',
            'durations.*' => 'string',
            'location_types' => 'required|array|min:1',
            'location_types.*' => 'string',
        ]);

        // 2. Добавляем служебные поля
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';
        $validated['created_at'] = now();

        // 3. Сохраняем в базу
        SuggestedIdea::create($validated);

        // 4. Возвращаем пользователя обратно с сообщением об успехе
        return redirect('/')->with('success', 'Спасибо! Твоя идея отправлена на рассмотрение.');
    }
}