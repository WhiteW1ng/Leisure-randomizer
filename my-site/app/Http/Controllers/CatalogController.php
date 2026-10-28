<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Support\IdeaFilter;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    // Публичный каталог одобренных занятий с фильтрами и страницами
    public function index(Request $request)
    {
        $validated = $request->validate(IdeaFilter::rules());
        $selected = IdeaFilter::selected($validated);

        $query = Idea::query()->where('status', 'active')->with(IdeaFilter::relations());
        IdeaFilter::apply($query, $selected);

        $ideas = $query->orderBy('title')->paginate(12)->withQueryString();

        return view('catalog.index', [
            'ideas'       => $ideas,
            'selected'    => $selected,
            'options'     => IdeaFilter::options(),
            'hasFilters'  => array_filter($selected) !== [],
            'favoriteIds' => auth()->check() ? auth()->user()->favoriteIdeaIds() : [],
        ]);
    }
}
