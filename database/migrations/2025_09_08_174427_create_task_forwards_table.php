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
        Schema::create('task_forwards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('forwarded_to')->constrained('users')->onDelete('cascade');
            $table->foreignId('forwarded_by')->constrained('users')->onDelete('cascade');
            $table->text('forward_reason');
            $table->timestamp('forwarded_at');
            $table->timestamps();
            
            // Index để tối ưu query
            $table->index(['task_id', 'forwarded_to']);
            $table->index(['forwarded_to', 'forwarded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_forwards');
    }
};