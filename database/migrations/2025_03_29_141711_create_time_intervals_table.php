<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('time_intervals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intervalable_id'); // ID связанной записи
            $table->string('intervalable_type');           // Тип связанной записи (Task или Habit)
            $table->timestamp('start_time');
            $table->timestamp('finish_time')->nullable();
            $table->integer('duration');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_intervals');
    }
};
