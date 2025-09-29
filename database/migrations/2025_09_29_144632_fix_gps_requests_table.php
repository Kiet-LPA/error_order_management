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
        // Đổi tên trường checkin_region_id thành department_id trong bảng gps_requests
        Schema::table('gps_requests', function (Blueprint $table) {
            $table->renameColumn('checkin_region_id', 'department_id');
        });
        
        // Thêm foreign key constraint mới cho department_id trong gps_requests
        Schema::table('gps_requests', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_requests', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->renameColumn('department_id', 'checkin_region_id');
        });
    }
};
