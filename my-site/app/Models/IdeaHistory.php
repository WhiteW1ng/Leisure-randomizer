<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaHistory extends Model
{
    protected $table = 'idea_histories';

    protected $fillable = ['user_id', 'idea_id'];

    // В таблице только created_at
    const UPDATED_AT = null;

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
