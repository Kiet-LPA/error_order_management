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
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('status');
            $table->date('recurring_start_date')->nullable()->after('is_recurring');
            $table->integer('recurring_days')->nullable()->after('recurring_start_date');
            $table->date('last_reset_date')->nullable()->after('recurring_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurring_start_date', 'recurring_days', 'last_reset_date']);
        });
    }
};
