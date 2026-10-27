<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budgets = [
            ['name' => 'бесплатно'],
            ['name' => 'недорого'],
            ['name' => 'дорого'],
        ];

        foreach ($budgets as $budget) {
            \App\Models\Budget::create($budget);
        }
    }
}
