<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $durations = [
            ['name' => 'до 1 часа'],
            ['name' => 'до 2 часов'],
            ['name' => 'до 3 часов'],
            ['name' => 'до 4 часов'],
            ['name' => 'полдня'],
            ['name' => 'день'],
            ['name' => 'несколько дней'],
        ];

        foreach ($durations as $duration) {
            \App\Models\Duration::create($duration);
        }
    }
}
