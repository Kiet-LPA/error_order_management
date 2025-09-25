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
        Schema::create('task_checklist_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to parent task
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            
            // Checklist item details
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false); // Item bắt buộc hay không
            
            // Assignment - chỉ được chọn trong danh sách assignees của task chính
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Order for display
            $table->integer('order')->default(0);
            
            // Status tracking
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('completion_note')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['task_id', 'is_completed']);
            $table->index(['assignee_id', 'is_completed']);
            $table->index(['task_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_checklist_items');
    }
};