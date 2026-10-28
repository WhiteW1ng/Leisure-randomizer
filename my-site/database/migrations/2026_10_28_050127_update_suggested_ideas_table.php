<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suggested_ideas', function (Blueprint $table) {
            // Удаляем старые текстовые поля
            $table->dropColumn(['season', 'company', 'budget']);
            
            // Добавляем JSON-поля для всех фильтров
            $table->json('seasons')->nullable();
            $table->json('companies')->nullable();
            $table->json('activity_levels')->nullable();
            $table->json('budgets')->nullable();
            $table->json('durations')->nullable();
            $table->json('location_types')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('suggested_ideas', function (Blueprint $table) {
            $table->dropColumn(['seasons', 'companies', 'activity_levels', 'budgets', 'durations', 'location_types']);
            $table->string('season')->nullable();
            $table->string('company')->nullable();
            $table->string('budget')->nullable();
        });
    }
};