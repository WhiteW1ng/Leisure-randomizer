<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seasons = [
            ['name' => 'зима'],
            ['name' => 'весна'],
            ['name' => 'лето'],
            ['name' => 'осень'],
        ];

        foreach ($seasons as $season) {
            \App\Models\Season::create($season);
        }
    }
}
