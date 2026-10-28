<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\IdeaHistory;
use App\Support\IdeaFilter;
use Illuminate\Http\Request;

class RandomIdeaController extends Controller
{
    // Публичная главная страница: доступна и гостям
    public function home(Request $request)
    {
        return view('welcome', $this->data($request));
    }

    // Дашборд для вошедших пользователей: тот же блок выбора
    public function dashboard(Request $request)
    {
        return view('dashboard', $this->data($request));
    }

    private function data(Request $request): array
    {
        $validated = $request->validate(
            ['mode' => ['nullable', 'in:any,filters']] + IdeaFilter::rules()
        );

        $mode = $validated['mode'] ?? null;      // any — без фильтров, filters — с фильтрами
        $useFilters = $mode === 'filters';
        $selected = IdeaFilter::selected($validated);

        $idea = null;
        if ($mode !== null) {
            // Берём только одобренные (активные) идеи
            $query = Idea::query()->where('status', 'active')->with(IdeaFilter::relations());

            if ($useFilters) {
                IdeaFilter::apply($query, $selected);
            }

            $idea = $query->inRandomOrder()->first();

            // Вошедшему пользователю записываем выпавшее занятие в историю
            if ($idea && auth()->check()) {
                IdeaHistory::create(['user_id' => auth()->id(), 'idea_id' => $idea->id]);
            }
        }

        return [
            'rolled'      => $mode !== null,
            'idea'        => $idea,
            'useFilters'  => $useFilters,
            'selected'    => $selected,
            'options'     => IdeaFilter::options(),
            'favoriteIds' => auth()->check() ? auth()->user()->favoriteIdeaIds() : [],
        ];
    }
}
