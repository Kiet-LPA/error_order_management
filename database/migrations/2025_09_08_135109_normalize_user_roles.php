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
        // Chuẩn hóa role trong database về lowercase và trim whitespace
        DB::statement("UPDATE users SET role = LOWER(TRIM(role))");
        
        // Log số lượng records đã được cập nhật
        $updatedCount = DB::table('users')->count();
        \Log::info("Normalized user roles: {$updatedCount} users updated");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không thể reverse vì không biết role gốc như thế nào
        // Chỉ log thông báo
        \Log::warning("Cannot reverse role normalization - original role values are unknown");
    }
};
