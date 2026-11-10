<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\IdeaHistory;
use App\Support\IdeaFilter;
use Illuminate\Http\Request;

class RandomIdeaController extends Controller
{
    // Главная страница: одна для всех (и для гостей, и для вошедших)
    public function home(Request $request)
    {
        return view('home', $this->data($request));
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
                auth()->user()->ideaHistory()->create(['idea_id' => $idea->id]);
            }
        }

        return [
            'total'       => Idea::where('status', 'active')->count(),
            'rolled'      => $mode !== null,
            'idea'        => $idea,
            'useFilters'  => $useFilters,
            'selected'    => $selected,
            'options'     => IdeaFilter::options(),
            'favoriteIds' => auth()->check() ? auth()->user()->favoriteIdeaIds() : [],
        ];
    }
}
