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

        if (!Schema::hasColumn('approval_requests', 'approvers')) {
            Schema::table('approval_requests', function (Blueprint $table) {
                if (Schema::hasColumn('approval_requests', 'current_approver_id')) {
                    $table->json('approvers')->nullable()->after('current_approver_id');
                } else {
                    $table->json('approvers')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('approval_requests') && Schema::hasColumn('approval_requests', 'approvers')) {
            Schema::table('approval_requests', function (Blueprint $table) {
                $table->dropColumn('approvers');
            });
        }
    }
};
