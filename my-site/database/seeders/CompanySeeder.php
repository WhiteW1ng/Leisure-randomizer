<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'с семьёй'],
            ['name' => 'с партнёром'],
            ['name' => 'с друзьями'],
            ['name' => 'один'],
        ];

        foreach ($companies as $company) {
            \App\Models\Company::create($company);
        }
    }
}
