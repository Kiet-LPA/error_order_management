<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month');
            $table->integer('week');
            $table->date('report_date');
            $table->text('daily_work');
            $table->text('difficulties')->nullable();
            $table->text('comments')->nullable();
            $table->json('custom_fields')->nullable(); // Cho phép mở rộng theo phòng ban
            $table->timestamps();
            
            // Đảm bảo mỗi user chỉ có 1 báo cáo cho 1 ngày cụ thể
            $table->unique(['user_id', 'report_date']);
            
            // Index để tối ưu truy vấn
            $table->index(['year', 'month', 'week']);
            $table->index(['department_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_reports');
    }
};
