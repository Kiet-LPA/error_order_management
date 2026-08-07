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
        if (!Schema::hasTable('approval_requests')) {
            return;
        }

        Schema::table('approval_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_requests', 'approved_by_id')) {
                $table->unsignedBigInteger('approved_by_id')->nullable();
            }
            if (!Schema::hasColumn('approval_requests', 'rejected_by_id')) {
                $table->unsignedBigInteger('rejected_by_id')->nullable();
            }
            if (!Schema::hasColumn('approval_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
        });

        try {
            Schema::table('approval_requests', function (Blueprint $table) {
                $table->foreign('approved_by_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // FK may already exist
        }

        try {
            Schema::table('approval_requests', function (Blueprint $table) {
                $table->foreign('rejected_by_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // FK may already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('approval_requests')) {
            return;
        }

        Schema::table('approval_requests', function (Blueprint $table) {
            try {
                $table->dropForeign(['approved_by_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['rejected_by_id']);
            } catch (\Throwable $e) {
            }

            $cols = array_values(array_filter([
                Schema::hasColumn('approval_requests', 'approved_by_id') ? 'approved_by_id' : null,
                Schema::hasColumn('approval_requests', 'rejected_by_id') ? 'rejected_by_id' : null,
                Schema::hasColumn('approval_requests', 'cancelled_at') ? 'cancelled_at' : null,
            ]));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
