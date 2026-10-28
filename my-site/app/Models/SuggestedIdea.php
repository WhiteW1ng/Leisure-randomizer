<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuggestedIdea extends Model
{
    protected $fillable = [
        'user_id', 
        'title', 
        'description', 
        'seasons',
        'companies',
        'activity_levels',
        'budgets',
        'durations',
        'location_types',
        'status', 
        'created_at'
    ];

    const UPDATED_AT = null;

    // Каст для автоматического преобразования JSON в массив и обратно
    protected $casts = [
        'seasons' => 'array',
        'companies' => 'array',
        'activity_levels' => 'array',
        'budgets' => 'array',
        'durations' => 'array',
        'location_types' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}