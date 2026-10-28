<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Support\IdeaFilter;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // Список избранного текущего пользователя
    public function index(Request $request)
    {
        $ideas = $request->user()->favoriteIdeas()
            ->where('ideas.status', 'active')
            ->with(IdeaFilter::relations())
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);

        return view('favorites.index', [
            'ideas'       => $ideas,
            'favoriteIds' => $request->user()->favoriteIdeaIds(),
        ]);
    }

    // Добавить в избранное или убрать оттуда (кнопка-форма POST с CSRF)
    public function toggle(Request $request, Idea $idea)
    {
        // В избранное можно добавлять только одобренные идеи
        abort_unless($idea->status === 'active', 404);

        $request->user()->favoriteIdeas()->toggle([$idea->id => ['created_at' => now()]]);

        return back();
    }
}
