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
        Schema::dropIfExists('forward_requests');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('forward_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('forwarded_by_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('forwarded_to_id')->constrained('users')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->timestamp('forwarded_at');
            $table->timestamps();
        });
    }
};
