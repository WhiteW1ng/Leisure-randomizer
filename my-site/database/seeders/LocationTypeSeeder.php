<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locationTypes = [
            ['name' => 'дома'],
            ['name' => 'в ином помещении'],
            ['name' => 'на улице'],
        ];

        foreach ($locationTypes as $locationType) {
            \App\Models\LocationType::create($locationType);
        }
    }
}
