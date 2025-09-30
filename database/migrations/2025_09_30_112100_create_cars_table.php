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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->unique();
            $table->decimal('weight', 8, 2);
            $table->string('car_type', 100);
            $table->string('color', 50);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'rented'])->default('active');
            $table->timestamp('available_from')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
