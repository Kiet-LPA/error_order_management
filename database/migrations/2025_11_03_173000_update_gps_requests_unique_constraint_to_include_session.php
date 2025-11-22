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
        Schema::table('gps_requests', function (Blueprint $table) {
            // Xóa unique constraint cũ
            $table->dropUnique('unique_request');
            
            // Tạo unique constraint mới bao gồm cả session
            // Cho phép 1 user có thể có 2 GPS requests trong 1 ngày (1 cho checkin, 1 cho checkout)
            $table->unique(['user_id', 'request_date', 'session'], 'unique_request');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_requests', function (Blueprint $table) {
            // Xóa unique constraint mới
            $table->dropUnique('unique_request');
            
            // Khôi phục unique constraint cũ
            $table->unique(['user_id', 'request_date'], 'unique_request');
        });
    }
};









