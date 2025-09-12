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
        // Cập nhật enum status để thêm 'cancelled'
        DB::statement("ALTER TABLE approval_requests MODIFY COLUMN status ENUM('draft', 'submitted', 'in_review', 'approved', 'rejected', 'cancelled') DEFAULT 'draft'");
        
        // Cập nhật enum approval_status để thêm 'cancelled'
        DB::statement("ALTER TABLE approval_requests MODIFY COLUMN approval_status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục enum status ban đầu
        DB::statement("ALTER TABLE approval_requests MODIFY COLUMN status ENUM('draft', 'submitted', 'in_review', 'approved', 'rejected') DEFAULT 'draft'");
        
        // Khôi phục enum approval_status ban đầu
        DB::statement("ALTER TABLE approval_requests MODIFY COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
