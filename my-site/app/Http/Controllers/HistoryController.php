<?php

namespace App\Http\Controllers;

use App\Models\IdeaHistory;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    // История выпавших занятий — только своя
    public function index(Request $request)
    {
        $entries = IdeaHistory::where('user_id', $request->user()->id)
            ->with('idea')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('history.index', compact('entries'));
    }

    // Очистить свою историю
    public function clear(Request $request)
    {
        IdeaHistory::where('user_id', $request->user()->id)->delete();

        return redirect()->route('history.index')->with('success', 'История очищена.');
    }
}
