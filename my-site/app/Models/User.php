<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// is_admin намеренно НЕ в списке fillable: нельзя стать админом через форму
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    // Избранные занятия (таблица favorites)
    public function favoriteIdeas(): BelongsToMany
    {
        return $this->belongsToMany(Idea::class, 'favorites', 'user_id', 'idea_id')
            ->withPivot('created_at');
    }

    /** Номера избранных идей — чтобы отметить кнопку «★» в списках */
    public function favoriteIdeaIds(): array
    {
        return $this->favoriteIdeas()->pluck('ideas.id')->all();
    }

    // История выпавших занятий
    public function ideaHistory(): HasMany
    {
        return $this->hasMany(IdeaHistory::class);
    }
}
