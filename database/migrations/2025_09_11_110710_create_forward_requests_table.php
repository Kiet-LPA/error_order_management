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
        Schema::create('forward_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_request_id');
            $table->unsignedBigInteger('from_user_id'); // Người chuyển tiếp
            $table->unsignedBigInteger('to_user_id'); // Người nhận
            $table->enum('level', ['employee', 'manager', 'director']); // Cấp độ
            $table->text('forward_note')->nullable(); // Ghi chú chuyển tiếp
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
            
            $table->foreign('approval_request_id')->references('id')->on('approval_requests')->onDelete('cascade');
            $table->foreign('from_user_id')->references('id')->on('users');
            $table->foreign('to_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forward_requests');
    }
};
