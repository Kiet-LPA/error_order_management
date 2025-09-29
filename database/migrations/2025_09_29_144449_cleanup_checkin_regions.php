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
        // Xóa trường checkin_region_id từ bảng users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['checkin_region_id']);
            $table->dropColumn('checkin_region_id');
        });
        
        // checkins và gps_requests đã được sửa trong migration trước
        
        // Xóa bảng checkin_regions
        Schema::dropIfExists('checkin_regions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không thể reverse một cách an toàn
        // Cần backup trước khi chạy migration này
    }
};
