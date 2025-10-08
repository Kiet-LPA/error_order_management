<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thay đổi enum session từ ['morning', 'evening'] thành ['checkin', 'checkout']
        DB::statement("ALTER TABLE checkins MODIFY COLUMN session ENUM('checkin', 'checkout') NOT NULL");
        
        // Cập nhật dữ liệu cũ nếu có
        DB::statement("UPDATE checkins SET session = 'checkin' WHERE session = 'morning'");
        DB::statement("UPDATE checkins SET session = 'checkout' WHERE session = 'evening'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cập nhật dữ liệu trước khi thay đổi enum
        DB::statement("UPDATE checkins SET session = 'morning' WHERE session = 'checkin'");
        DB::statement("UPDATE checkins SET session = 'evening' WHERE session = 'checkout'");
        
        // Khôi phục enum cũ
        DB::statement("ALTER TABLE checkins MODIFY COLUMN session ENUM('morning', 'evening') NOT NULL");
    }
};