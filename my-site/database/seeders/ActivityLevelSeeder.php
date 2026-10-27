<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivityLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'активно'],
            ['name' => 'умеренно'],
            ['name' => 'пассивно'],
        ];

        foreach ($levels as $level) {
            \App\Models\ActivityLevel::create($level);
        }
    }
}
