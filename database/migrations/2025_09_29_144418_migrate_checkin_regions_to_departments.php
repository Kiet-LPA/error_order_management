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
        // Di chuyển dữ liệu từ checkin_regions sang departments
        $regions = DB::table('checkin_regions')->get();
        
        foreach ($regions as $region) {
            // Tìm hoặc tạo department với tên tương ứng
            $department = DB::table('departments')
                ->where('name', $region->name)
                ->first();
            
            if (!$department) {
                // Tạo department mới
                $departmentId = DB::table('departments')->insertGetId([
                    'name' => $region->name,
                    'latitude' => $region->latitude,
                    'longitude' => $region->longitude,
                    'radius_meters' => $region->radius_meters,
                    'address' => $region->address,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Cập nhật department hiện có
                DB::table('departments')
                    ->where('id', $department->id)
                    ->update([
                        'latitude' => $region->latitude,
                        'longitude' => $region->longitude,
                        'radius_meters' => $region->radius_meters,
                        'address' => $region->address,
                        'updated_at' => now(),
                    ]);
                $departmentId = $department->id;
            }
            
            // Cập nhật users có checkin_region_id tương ứng
            DB::table('users')
                ->where('checkin_region_id', $region->id)
                ->update(['department_id' => $departmentId]);
            
            // Cập nhật checkins table để sử dụng department thay vì checkin_region
            DB::table('checkins')
                ->where('checkin_region_id', $region->id)
                ->update(['checkin_region_id' => $departmentId]);
            
            // Cập nhật gps_requests table
            DB::table('gps_requests')
                ->where('checkin_region_id', $region->id)
                ->update(['checkin_region_id' => $departmentId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không thể reverse một cách an toàn vì đã thay đổi cấu trúc
        // Cần backup trước khi chạy migration này
    }
};
