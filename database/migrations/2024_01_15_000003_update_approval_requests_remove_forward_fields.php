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
            // Remove forward-related fields since we're replacing with multi-approver system
            // Keep current_approver_id for backward compatibility but it will be used differently
            
            // Add new fields for multi-approver system
            $table->enum('approval_type', ['single', 'multiple', 'all_required'])->default('single')->after('approval_status');
            $table->boolean('require_all_approvals')->default(false)->after('approval_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn(['approval_type', 'require_all_approvals']);
        });
    }
};
