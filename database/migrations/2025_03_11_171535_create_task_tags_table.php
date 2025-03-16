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
        Schema::create('task_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id_task')->constrained('tasks', 'id_task')->onDelete('cascade');            
            $table->foreignId('tag_id')->constrained();

            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_tags');
    }
};
