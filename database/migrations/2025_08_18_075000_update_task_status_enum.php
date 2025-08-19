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
            // Add new enum with updated statuses
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'approved', 'rejected', 'overdue'])->default('assigned');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');
        });
    }
};
