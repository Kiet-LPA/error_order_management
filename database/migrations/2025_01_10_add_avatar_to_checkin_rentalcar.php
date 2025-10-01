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
        // Thêm avatar vào bảng users của checkin_new database
        DB::connection('mysql')->statement('
            ALTER TABLE checkin_new.users 
            ADD COLUMN avatar VARCHAR(255) NULL AFTER full_name
        ');
        
        // Thêm avatar vào bảng users của rentalcar database (nếu có)
        try {
            DB::connection('mysql')->statement('
                ALTER TABLE rentalcar.users 
                ADD COLUMN avatar VARCHAR(255) NULL AFTER name
            ');
        } catch (\Exception $e) {
            // Database rentalcar có thể chưa tồn tại
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::connection('mysql')->statement('
                ALTER TABLE checkin_new.users DROP COLUMN avatar
            ');
        } catch (\Exception $e) {
            //
        }
        
        try {
            DB::connection('mysql')->statement('
                ALTER TABLE rentalcar.users DROP COLUMN avatar
            ');
        } catch (\Exception $e) {
            //
        }
    }
};

