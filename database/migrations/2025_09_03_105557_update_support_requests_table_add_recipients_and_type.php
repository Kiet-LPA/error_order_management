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
        Schema::table('support_requests', function (Blueprint $table) {
            // Thay đổi approver_id thành recipients (JSON array)
            $table->json('recipients')->nullable()->after('approver_id');
            
            // Thêm trường request_type để phân biệt loại yêu cầu
            $table->enum('request_type', ['employee', 'manager'])->default('employee')->after('recipients');
            
            // Thêm trường source_department_id để biết yêu cầu từ phòng ban nào
            $table->unsignedBigInteger('source_department_id')->nullable()->after('department_id');
            
            // Thêm trường forwarded_by để biết ai đã chuyển tiếp yêu cầu
            $table->unsignedBigInteger('forwarded_by')->nullable()->after('source_department_id');
            
            // Thêm trường forwarding_reason để ghi lý do chuyển tiếp
            $table->text('forwarding_reason')->nullable()->after('forwarded_by');
            
            // Thêm foreign key constraints
            $table->foreign('source_department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('forwarded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_requests', function (Blueprint $table) {
            $table->dropForeign(['source_department_id']);
            $table->dropForeign(['forwarded_by']);
            $table->dropColumn([
                'recipients',
                'request_type', 
                'source_department_id',
                'forwarded_by',
                'forwarding_reason'
            ]);
        });
    }
};
