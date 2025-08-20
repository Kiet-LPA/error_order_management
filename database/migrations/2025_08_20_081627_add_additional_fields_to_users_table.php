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
            $table->string('social_insurance_number')->nullable()->after('position'); // Mã số BHXH
            $table->string('health_insurance_number')->nullable()->after('social_insurance_number'); // Mã số BHYT
            $table->string('personal_identification_number')->nullable()->after('health_insurance_number'); // Mã số định danh cá nhân
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['social_insurance_number', 'health_insurance_number', 'personal_identification_number']);
        });
    }
};
