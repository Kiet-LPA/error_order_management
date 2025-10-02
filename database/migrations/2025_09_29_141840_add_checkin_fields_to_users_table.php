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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'checkin_region_id')) {
                $table->unsignedBigInteger('checkin_region_id')->nullable()->after('department_id');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('checkin_region_id');
            }
        });
        
        // Thêm foreign key constraint sau khi bảng checkin_regions được tạo
        if (Schema::hasTable('checkin_regions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('checkin_region_id')->references('id')->on('checkin_regions')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['checkin_region_id']);
            $table->dropColumn(['checkin_region_id', 'is_active']);
        });
    }
};
