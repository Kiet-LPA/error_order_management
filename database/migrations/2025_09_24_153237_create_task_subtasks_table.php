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
        Schema::create('task_subtasks', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to parent task
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            
            // Sub-task details
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'completed'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            
            // Assignment - chỉ được chọn trong danh sách assignees của task chính
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Deadline for sub-task
            $table->timestamp('deadline')->nullable();
            
            // Completion tracking
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_note')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Order for display
            $table->integer('order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['task_id', 'status']);
            $table->index(['assignee_id', 'status']);
            $table->index(['task_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_subtasks');
    }
};