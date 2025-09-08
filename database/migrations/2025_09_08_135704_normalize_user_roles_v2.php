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
        // Chuẩn hóa role trong database - loại bỏ khoảng trắng và chuyển về lowercase
        DB::table('users')->update([
            'role' => DB::raw("LOWER(TRIM(role))")
        ]);
        
        // Log số lượng records đã được cập nhật
        $updatedCount = DB::table('users')->count();
        \Log::info("Normalized user roles v2: {$updatedCount} users updated");
        
        // Log chi tiết các role sau khi chuẩn hóa
        $roles = DB::table('users')->select('role')->distinct()->pluck('role');
        \Log::info("Available roles after normalization: " . $roles->implode(', '));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không thể reverse vì không biết role gốc như thế nào
        \Log::warning("Cannot reverse role normalization v2 - original role values are unknown");
    }
};
