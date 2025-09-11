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
        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_request_id');
            $table->unsignedBigInteger('user_id'); // Người thực hiện action
            $table->enum('action', ['approve', 'reject']); // Hành động
            $table->text('note')->nullable(); // Ghi chú
            $table->timestamp('action_at'); // Thời gian thực hiện
            $table->timestamps();
            
            $table->foreign('approval_request_id')->references('id')->on('approval_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
    }
};
