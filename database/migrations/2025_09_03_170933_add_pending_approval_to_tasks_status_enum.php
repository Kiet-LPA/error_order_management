<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop enum constraint first
            $table->dropColumn('status');
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            // Add new enum with pending_approval status
            $table->enum('status', ['in_progress', 'completed', 'rejected', 'overdue', 'finished', 'pending_approval'])->default('in_progress');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            // Revert to original enum without pending_approval
            $table->enum('status', ['in_progress', 'completed', 'rejected', 'overdue', 'finished'])->default('in_progress');
        });
    }
};