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
        Schema::create('approval_forms', function (Blueprint $table) {
            $table->id();
            $table->string('form_type')->unique(); // payment_request, purchase_request, etc.
            $table->string('form_name'); // Tên hiển thị của form
            $table->text('description')->nullable(); // Mô tả form
            $table->json('form_fields'); // Cấu hình các field của form
            $table->json('validation_rules')->nullable(); // Rules validation
            $table->json('approval_workflow')->nullable(); // Quy trình phê duyệt
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_forms');
    }
};
