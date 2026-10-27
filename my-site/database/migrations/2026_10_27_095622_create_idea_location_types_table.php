<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('idea_location_types', function (Blueprint $table) {
             $table->foreignId('idea_id')->constrained('ideas')->cascadeOnDelete();
            $table->foreignId('location_type_id')->constrained('location_types')->cascadeOnDelete();
            $table->primary(['idea_id', 'location_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idea_location_types');
    }
};
