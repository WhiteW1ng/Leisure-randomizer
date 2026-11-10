<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuggestedIdea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuggestedIdeasController extends Controller
{
    // Показывает список идей на модерации
    public function index()
    {
        $ideas = SuggestedIdea::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.suggested-ideas.index', compact('ideas'));
    }

    // Одобрить идею и перенести в основную базу
    public function approve($id)
    {
<<<<<<< HEAD
        // Всё в одной транзакции: двойной клик или повторный запрос не создаст дубль идеи
        return DB::transaction(function () use ($id) {
            $suggested = SuggestedIdea::whereKey($id)->lockForUpdate()->firstOrFail();

            // Рассматриваем только то, что ещё на рассмотрении
            if ($suggested->status !== 'pending') {
                return redirect()->back()->with('error', 'Эта идея уже рассмотрена.');
            }

=======
        $suggested = SuggestedIdea::findOrFail($id);
        
>>>>>>> c8bb101774e5a91fd1eb9bfa65342f30c67ce747
        // 1. Определяем основной тип локации (берём первое значение из массива)
        $mainLocationType = !empty($suggested->location_types) 
            ? $suggested->location_types[0] 
            : null; // Если не выбрано - оставляем NULL

        // 2. Создаем новую запись в основной таблице ideas
        $newIdea = \App\Models\Idea::create([
            'title' => $suggested->title,
            'description' => $suggested->description,
            'location_type' => $mainLocationType, // Берём из формы или NULL
            'status' => 'active',
            'created_at' => now(),
        ]);

        // 3. Привязываем сезоны (только если выбраны)
        if (!empty($suggested->seasons)) {
            $seasonIds = \App\Models\Season::whereIn('name', $suggested->seasons)->pluck('id');
            $newIdea->seasons()->attach($seasonIds);
        }

        // 4. Привязываем компании (только если выбраны)
        if (!empty($suggested->companies)) {
            $companyIds = \App\Models\Company::whereIn('name', $suggested->companies)->pluck('id');
            $newIdea->companies()->attach($companyIds);
        }

        // 5. Привязываем уровни активности (только если выбраны)
        if (!empty($suggested->activity_levels)) {
            $levelIds = \App\Models\ActivityLevel::whereIn('name', $suggested->activity_levels)->pluck('id');
            $newIdea->activityLevels()->attach($levelIds);
        }

        // 6. Привязываем бюджеты (только если выбраны)
        if (!empty($suggested->budgets)) {
            $budgetIds = \App\Models\Budget::whereIn('name', $suggested->budgets)->pluck('id');
            $newIdea->budgets()->attach($budgetIds);
        }

        // 7. Привязываем длительности (только если выбраны)
        if (!empty($suggested->durations)) {
            $durationIds = \App\Models\Duration::whereIn('name', $suggested->durations)->pluck('id');
            $newIdea->durations()->attach($durationIds);
        }

        // 8. Привязываем типы локаций (только если выбраны)
        if (!empty($suggested->location_types)) {
            $locationIds = \App\Models\LocationType::whereIn('name', $suggested->location_types)->pluck('id');
            $newIdea->locationTypes()->attach($locationIds);
        }

        // 9. Меняем статус в таблице предложений
        $suggested->update(['status' => 'approved']);
        \App\Models\AdminLog::logAction('approve', 'SuggestedIdea', $idea->id, "Идея: {$idea->id->title}");
        return redirect()->back()->with('success', "Идея «{$suggested->title}» одобрена и добавлена на сайт!");
        });
    }

    // Отклонить идею
    public function reject($id)
    {
        $idea = SuggestedIdea::findOrFail($id);

        if ($idea->status !== 'pending') {
            return redirect()->back()->with('error', 'Эта идея уже рассмотрена.');
        }

        $idea->update(['status' => 'rejected']);
        \App\Models\AdminLog::logAction('reject', 'SuggestedIdea', $idea->id, "Идея: {$idea->title}");
        return redirect()->back()->with('success', "Идея «{$idea->title}» отклонена.");
    }
}