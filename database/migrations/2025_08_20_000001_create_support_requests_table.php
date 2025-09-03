<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->json('attachments')->nullable();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
