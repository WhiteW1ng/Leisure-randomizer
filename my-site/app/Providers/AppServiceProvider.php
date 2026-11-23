<?php

namespace App\Providers;

use App\Models\Idea;
use App\Models\SuggestedIdea;
use App\Support\Statuses;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // На боевом сервере все ссылки и редиректы строим только по https
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => Password::min(10));

        // Номера в адресах (/favorites/{idea}, /admin/suggested-ideas/{id}/...) — только цифры.
        // Иначе адрес с буквами сразу даёт 404, до обращения к базе.
        Route::pattern('id', '[0-9]+');
        Route::pattern('idea', '[0-9]+');

        // Статус можно сохранить только из белого списка
        SuggestedIdea::saving(fn (SuggestedIdea $m) => Statuses::check($m->status, Statuses::SUGGESTED));
        Idea::saving(fn (Idea $m) => Statuses::check($m->status, Statuses::IDEA));
    }
}
