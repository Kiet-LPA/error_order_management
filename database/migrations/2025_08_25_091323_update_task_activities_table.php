<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('task_activities', function (Blueprint $table) {
            // Thêm các trường mới cho real-time và notifications
            $table->json('notifications')->nullable(); // Lưu thông tin notifications
            $table->boolean('is_read')->default(false); // Đánh dấu đã đọc
            $table->timestamp('read_at')->nullable(); // Thời gian đọc
            $table->string('ip_address')->nullable(); // IP của user thực hiện
            $table->string('user_agent')->nullable(); // User agent
            
            // Index cho performance
            $table->index(['task_id', 'action', 'created_at']);
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down()
    {
        Schema::table('task_activities', function (Blueprint $table) {
            $table->dropColumn(['notifications', 'is_read', 'read_at', 'ip_address', 'user_agent']);
            $table->dropIndex(['task_id', 'action', 'created_at']);
            $table->dropIndex(['user_id', 'is_read']);
        });
    }
};
