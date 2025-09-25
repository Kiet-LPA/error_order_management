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
        Schema::create('task_checklist_completions', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('task_checklist_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Completion details
            $table->enum('action', ['completed', 'uncompleted']); // completed hoặc uncompleted
            $table->text('note')->nullable();
            $table->timestamp('completed_at');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['task_id', 'user_id']);
            $table->index(['checklist_item_id', 'action']);
            $table->index(['completed_at']);
            
            // Unique constraint để tránh duplicate
            $table->unique(['checklist_item_id', 'user_id', 'completed_at'], 'checklist_completion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_checklist_completions');
    }
};