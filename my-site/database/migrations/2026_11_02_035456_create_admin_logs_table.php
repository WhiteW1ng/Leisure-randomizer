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
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // КТО (админ)
            $table->string('action');        // ЧТО СДЕЛАЛ (approve, reject)
            $table->string('model_type');    // С КАКОЙ ТАБЛИЦЕЙ (SuggestedIdea)
            $table->unsignedBigInteger('model_id'); // ID ЗАПИСИ
            $table->text('details')->nullable();    // ПОДРОБНОСТИ (например, название идеи)
            $table->timestamps();            // КОГДА
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
