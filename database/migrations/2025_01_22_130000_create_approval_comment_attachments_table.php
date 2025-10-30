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
        // Kiểm tra xem bảng đã tồn tại chưa
        if (!Schema::hasTable('approval_comment_attachments')) {
            Schema::create('approval_comment_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('approval_comment_id')->constrained()->onDelete('cascade');
                $table->string('original_name'); // Tên file gốc
                $table->string('file_name'); // Tên file trên server
                $table->string('file_path'); // Đường dẫn file
                $table->string('file_url'); // URL để truy cập
                $table->string('mime_type');
                $table->bigInteger('file_size'); // Kích thước file (bytes)
                $table->string('file_extension');
                $table->json('meta')->nullable(); // Thông tin bổ sung (dimensions, duration, etc.)
                $table->timestamps();
                
                $table->index(['approval_comment_id', 'created_at']);
                $table->index(['mime_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_comment_attachments');
    }
};
