<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
    protected $fillable = [
        'title', 
        'description', 
        'status', 
        'created_at', 
        'location_type'
    ];

    // У таблицы ideas есть только created_at, без updated_at
    const UPDATED_AT = null;

    // Связи с фильтрами
    public function seasons()
    {
        return $this->belongsToMany(Season::class, 'idea_seasons');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'idea_companies');
    }

    public function activityLevels()
    {
        return $this->belongsToMany(ActivityLevel::class, 'idea_activity_levels');
    }

    public function budgets()
    {
        return $this->belongsToMany(Budget::class, 'idea_budgets');
    }

    public function durations()
    {
        return $this->belongsToMany(Duration::class, 'idea_durations');
    }

    public function locationTypes()
    {
        return $this->belongsToMany(LocationType::class, 'idea_location_types');
    }
}
