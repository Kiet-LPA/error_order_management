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
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by_id')->nullable()->after('current_approver_id');
            $table->unsignedBigInteger('rejected_by_id')->nullable()->after('approved_by_id');
            $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            
            // Add foreign key constraints
            $table->foreign('approved_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_id']);
            $table->dropForeign(['rejected_by_id']);
            $table->dropColumn(['approved_by_id', 'rejected_by_id', 'cancelled_at']);
        });
    }
};
