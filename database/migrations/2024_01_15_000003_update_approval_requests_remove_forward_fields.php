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
            if (!Schema::hasColumn('approval_requests', 'approval_type')) {
                $table->enum('approval_type', ['single', 'multiple', 'all_required'])->default('single');
            }
            if (!Schema::hasColumn('approval_requests', 'require_all_approvals')) {
                $table->boolean('require_all_approvals')->default(false);
            }
        });
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
            $cols = [];
            if (Schema::hasColumn('approval_requests', 'approval_type')) {
                $cols[] = 'approval_type';
            }
            if (Schema::hasColumn('approval_requests', 'require_all_approvals')) {
                $cols[] = 'require_all_approvals';
            }
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
