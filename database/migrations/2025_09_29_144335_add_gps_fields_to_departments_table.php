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
        Schema::table('departments', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('name');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('radius_meters')->default(200)->after('longitude');
            $table->text('address')->nullable()->after('radius_meters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_meters', 'address']);
        });
    }
};
