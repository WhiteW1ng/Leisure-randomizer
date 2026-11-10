<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{

    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function favoriteIdeas(): BelongsToMany
    {
        return $this->belongsToMany(Idea::class, 'favorites', 'user_id', 'idea_id')
            ->withPivot('created_at');
    }

    public function favoriteIdeaIds(): array
    {
        return $this->favoriteIdeas()->pluck('ideas.id')->all();
    }

    public function ideaHistory(): HasMany
    {
        return $this->hasMany(IdeaHistory::class);
    }
}
