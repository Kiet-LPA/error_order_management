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
        if (Schema::hasTable('task_submissions')) {
            return;
        }

        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('submitted_at')->nullable(); // Khi user submit
            $table->timestamp('undone_at')->nullable(); // Khi user hoàn tác
            $table->enum('status', ['pending', 'submitted', 'undone'])->default('pending');
            $table->timestamps();
            
            // Đảm bảo mỗi user chỉ có 1 record cho 1 task
            $table->unique(['task_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};