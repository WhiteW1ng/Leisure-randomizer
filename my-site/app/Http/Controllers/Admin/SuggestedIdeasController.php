<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\ActivityLevel;
use App\Models\AdminLog;
use App\Models\Budget;
use App\Models\Company;
use App\Models\Duration;
use App\Models\Idea;
use App\Models\LocationType;
use App\Models\Season;
use App\Models\SuggestedIdea;
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
        // Всё в одной транзакции: двойной клик или повторный запрос не создаст дубль идеи
        return DB::transaction(function () use ($id) {
            $suggested = SuggestedIdea::whereKey($id)->lockForUpdate()->firstOrFail();
 
            // Рассматриваем только то, что ещё на рассмотрении
            if ($suggested->status !== 'pending') {
                return redirect()->back()->with('error', 'Эта идея уже рассмотрена.');
            }
 
            // 1. Основной тип локации (первое значение из массива)
            $mainLocationType = !empty($suggested->location_types)
                ? $suggested->location_types[0]
                : null;
 
            // 2. Новая запись в основной таблице ideas
            $newIdea = Idea::create([
                'title' => $suggested->title,
                'description' => $suggested->description,
                'location_type' => $mainLocationType,
                'status' => 'active',
                'created_at' => now(),
            ]);
 
            // 3. Привязываем фильтры (только если выбраны)
            if (!empty($suggested->seasons)) {
                $newIdea->seasons()->attach(Season::whereIn('name', $suggested->seasons)->pluck('id'));
            }
            if (!empty($suggested->companies)) {
                $newIdea->companies()->attach(Company::whereIn('name', $suggested->companies)->pluck('id'));
            }
            if (!empty($suggested->activity_levels)) {
                $newIdea->activityLevels()->attach(ActivityLevel::whereIn('name', $suggested->activity_levels)->pluck('id'));
            }
            if (!empty($suggested->budgets)) {
                $newIdea->budgets()->attach(Budget::whereIn('name', $suggested->budgets)->pluck('id'));
            }
            if (!empty($suggested->durations)) {
                $newIdea->durations()->attach(Duration::whereIn('name', $suggested->durations)->pluck('id'));
            }
            if (!empty($suggested->location_types)) {
                $newIdea->locationTypes()->attach(LocationType::whereIn('name', $suggested->location_types)->pluck('id'));
            }
 
            // 4. Меняем статус заявки
            $suggested->update(['status' => 'approved']);
 
            // 5. Журнал админских действий (здесь переменная называется $suggested, а не $idea)
            AdminLog::logAction('approve', 'SuggestedIdea', $suggested->id, "Идея: {$suggested->title}");
 
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
 
        AdminLog::logAction('reject', 'SuggestedIdea', $idea->id, "Идея: {$idea->title}");
 
        return redirect()->back()->with('success', "Идея «{$idea->title}» отклонена.");
    }
}
 