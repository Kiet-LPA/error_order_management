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
        // Migrate dữ liệu từ department_id sang user_departments
        $users = DB::table('users')->whereNotNull('department_id')->get();
        
        foreach ($users as $user) {
            // Kiểm tra xem đã có trong user_departments chưa
            $exists = DB::table('user_departments')
                ->where('user_id', $user->id)
                ->where('department_id', $user->department_id)
                ->exists();
                
            if (!$exists) {
                DB::table('user_departments')->insert([
                    'user_id' => $user->id,
                    'department_id' => $user->department_id,
                    'is_primary' => true, // Phòng ban hiện tại là phòng ban chính
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa dữ liệu trong user_departments
        DB::table('user_departments')->truncate();
    }
};
