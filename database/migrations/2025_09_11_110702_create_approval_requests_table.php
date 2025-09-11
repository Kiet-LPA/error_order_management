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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('form_type'); // Loại form
            $table->json('form_data'); // Dữ liệu form đã điền
            $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected'])->default('draft');
            $table->enum('discussion_status', ['open', 'closed'])->default('open');
            $table->enum('edit_status', ['editable', 'locked'])->default('editable');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('created_by_id'); // Người tạo
            $table->unsignedBigInteger('current_approver_id')->nullable(); // Người hiện tại cần phê duyệt
            $table->json('approval_signatures')->nullable(); // Chữ ký phê duyệt
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by_id')->references('id')->on('users');
            $table->foreign('current_approver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
