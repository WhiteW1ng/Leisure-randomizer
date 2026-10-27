<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IdeasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
    // 1. Домашний киновечер
        $idea1 = \App\Models\Idea::create([
            'title' => 'Домашний киновечер',
            'description' => 'Плед, какао и интересный фильм — идеальный вечер без спешки и суеты.',
            'status' => 'active',
            'location_type' => 'дома',
            'created_at' => now(),
        ]);
    
        // Привязываем фильтры
        $idea1->seasons()->attach(\App\Models\Season::whereIn('name', ['зима', 'весна', 'лето', 'осень'])->pluck('id'));
        $idea1->companies()->attach(\App\Models\Company::whereIn('name', ['с семьёй', 'с партнёром', 'с друзьями', 'один'])->pluck('id'));
        $idea1->activityLevels()->attach(\App\Models\ActivityLevel::where('name', 'пассивно')->value('id'));
        $idea1->budgets()->attach(\App\Models\Budget::where('name', 'бесплатно')->value('id'));
        $idea1->durations()->attach(\App\Models\Duration::where('name', 'до 2 часов')->value('id'));
        $idea1->locationTypes()->attach(\App\Models\LocationType::where('name', 'дома')->value('id'));

    // 2. Велопрогулка на набережной
        $idea2 = \App\Models\Idea::create([
            'title' => 'Велопрогулка на набережной',
            'description' => 'Ветер в лицо, свежий бриз. А если нет своего велосипеда — возьми в аренду.',
            'status' => 'active',
            'location_type' => 'на улице',
            'created_at' => now(),
        ]);
    
        $idea2->seasons()->attach(\App\Models\Season::where('name', 'лето')->value('id'));
        $idea2->companies()->attach(\App\Models\Company::whereIn('name', ['с семьёй', 'с партнёром', 'с друзьями', 'один'])->pluck('id'));
        $idea2->activityLevels()->attach(\App\Models\ActivityLevel::where('name', 'активно')->value('id'));
        $idea2->budgets()->attach(\App\Models\Budget::whereIn('name', ['бесплатно', 'недорого'])->pluck('id'));
        $idea2->durations()->attach(\App\Models\Duration::where('name', 'до 2 часов')->value('id'));
        $idea2->locationTypes()->attach(\App\Models\LocationType::where('name', 'на улице')->value('id'));

    // 3. Гончарный мастер-класс
        $idea3 = \App\Models\Idea::create([
            'title' => 'Гончарный мастер-класс',
            'description' => 'От мягкого кома глины к готовой форме.',
            'status' => 'active',
            'location_type' => 'в ином помещении',
            'created_at' => now(),
        ]);
    
        $idea3->seasons()->attach(\App\Models\Season::whereIn('name', ['зима', 'весна', 'лето', 'осень'])->pluck('id'));
        $idea3->companies()->attach(\App\Models\Company::whereIn('name', ['с семьёй', 'с партнёром', 'с друзьями', 'один'])->pluck('id'));
        $idea3->activityLevels()->attach(\App\Models\ActivityLevel::where('name', 'умеренно')->value('id'));
        $idea3->budgets()->attach(\App\Models\Budget::where('name', 'дорого')->value('id'));
        $idea3->durations()->attach(\App\Models\Duration::where('name', 'до 3 часов')->value('id'));
        $idea3->locationTypes()->attach(\App\Models\LocationType::where('name', 'в ином помещении')->value('id'));

    // 4. Раскрасить картину по номерам
        $idea4 = \App\Models\Idea::create([
            'title' => 'Раскрасить картину по номерам',
            'description' => 'Собери шедевр из красочных фрагментов — без таланта художника, с душой создателя.',
            'status' => 'active',
            'location_type' => 'дома',
            'created_at' => now(),
        ]);
        
        $idea4->seasons()->attach(\App\Models\Season::whereIn('name', ['зима', 'весна', 'лето', 'осень'])->pluck('id'));
        $idea4->companies()->attach(\App\Models\Company::whereIn('name', ['с семьёй', 'с партнёром', 'с друзьями', 'один'])->pluck('id'));
        $idea4->activityLevels()->attach(\App\Models\ActivityLevel::where('name', 'пассивно')->value('id'));
        $idea4->budgets()->attach(\App\Models\Budget::where('name', 'недорого')->value('id'));
        $idea4->durations()->attach(\App\Models\Duration::where('name', 'несколько дней')->value('id'));
        $idea4->locationTypes()->attach(\App\Models\LocationType::where('name', 'дома')->value('id'));

    // 5. Прогулка в парке
        $idea5 = \App\Models\Idea::create([
            'title' => 'Прогулка в парке',
            'description' => 'Свежий воздух — то, что нужно.',
            'status' => 'active',
            'location_type' => 'на улице',
            'created_at' => now(),
        ]);
    
        $idea5->seasons()->attach(\App\Models\Season::whereIn('name', ['весна', 'лето', 'осень'])->pluck('id'));
        $idea5->companies()->attach(\App\Models\Company::whereIn('name', ['с семьёй', 'с партнёром', 'с друзьями', 'один'])->pluck('id'));
        $idea5->activityLevels()->attach(\App\Models\ActivityLevel::where('name', 'умеренно')->value('id'));
        $idea5->budgets()->attach(\App\Models\Budget::where('name', 'бесплатно')->value('id'));
        $idea5->durations()->attach(\App\Models\Duration::where('name', 'до 2 часов')->value('id'));
        $idea5->locationTypes()->attach(\App\Models\LocationType::where('name', 'на улице')->value('id'));
    }
}
