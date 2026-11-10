<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    // Белый список полей
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'details'
    ];

    // Связь с пользователем (чтобы можно было вывести имя админа)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Удобный хелпер для быстрой записи в лог из любого контроллера
     */
    public static function logAction(string $action, string $modelType, int $modelId, ?string $details = null): void
    {
        self::create([
            'user_id' => auth()->id(), // КТО: берём строго из сессии, не из запроса!
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'details' => $details,
        ]);
    }
}