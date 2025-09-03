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
        Schema::table('users', function (Blueprint $table) {
            // Cập nhật enum role để bao gồm 'director'
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'director', 'manager', 'employee') DEFAULT 'employee'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert về enum cũ
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'employee') DEFAULT 'employee'");
        });
    }
};
