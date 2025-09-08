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
            $table->unsignedBigInteger('forwarded_to')->nullable()->after('is_multi_department');
            $table->unsignedBigInteger('forwarded_by')->nullable()->after('forwarded_to');
            $table->text('forward_reason')->nullable()->after('forwarded_by');
            $table->timestamp('forwarded_at')->nullable()->after('forward_reason');
            
            $table->foreign('forwarded_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('forwarded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['forwarded_to']);
            $table->dropForeign(['forwarded_by']);
            $table->dropColumn(['forwarded_to', 'forwarded_by', 'forward_reason', 'forwarded_at']);
        });
    }
};