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
        if (!Schema::hasTable('gps_requests')) {
            return;
        }

        Schema::table('gps_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('gps_requests', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('distance_meters');
            }
            if (!Schema::hasColumn('gps_requests', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('gps_requests')) {
            return;
        }

        Schema::table('gps_requests', function (Blueprint $table) {
            $cols = array_values(array_filter([
                Schema::hasColumn('gps_requests', 'latitude') ? 'latitude' : null,
                Schema::hasColumn('gps_requests', 'longitude') ? 'longitude' : null,
            ]));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
