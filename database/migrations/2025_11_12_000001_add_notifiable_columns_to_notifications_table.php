<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('notifiable_id')->nullable()->after('user_id');
            $table->string('notifiable_type')->nullable()->after('notifiable_id');

            $table->index(['notifiable_type', 'notifiable_id']);
        });

        // Backfill existing notifications so they conform to Laravel's morph columns
        DB::table('notifications')
            ->whereNull('notifiable_type')
            ->update([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id' => DB::raw('user_id'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_notifiable_type_notifiable_id_index');
            $table->dropColumn(['notifiable_type', 'notifiable_id']);
        });
    }
};

