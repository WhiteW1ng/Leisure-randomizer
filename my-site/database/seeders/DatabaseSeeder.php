<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Заполняет справочники фильтров и начальные идеи.
     * Запускать один раз на чистой базе: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            SeasonSeeder::class,
            CompanySeeder::class,
            ActivityLevelSeeder::class,
            BudgetSeeder::class,
            DurationSeeder::class,
            LocationTypeSeeder::class,
            IdeasSeeder::class,
        ]);
    }
}
