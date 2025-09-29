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
            $table->foreignId('checkin_region_id')->nullable()->after('department_id')
                  ->constrained('checkin_regions')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('checkin_region_id');
        });
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
