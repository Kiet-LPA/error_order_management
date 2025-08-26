<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\EmployeeContract;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả phòng ban
        $departments = Department::all();
        
        if ($departments->count() == 0) {
            $this->command->error('Không có phòng ban nào. Vui lòng chạy DepartmentSeeder trước.');
            return;
        }

        // Tạo 12 nhân viên thử việc
        for ($i = 1; $i <= 12; $i++) {
            // Chia đều cho 4 phòng ban (0-3, 4-7, 8-11)
            $departmentIndex = ($i - 1) % $departments->count();
            $department = $departments[$departmentIndex];
            
            $user = User::create([
                'name' => "Thử việc {$i}",
                'email' => "thuviec{$i}@gmail.com",
                'phone' => "123321123{$i}",
                'password' => Hash::make("thuviec{$i}"),
                'role' => 'employee',
                'department_id' => $department->id,
                'employee_type' => 'new',
                'position' => 'Nhân viên thử việc',
            ]);

            // Tạo hợp đồng thử việc
            EmployeeContract::create([
                'user_id' => $user->id,
                'probation_salary' => 5000000, // 5 triệu VNĐ
                'probation_period' => 2, // 2 tháng
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
                'status' => 'active',
            ]);

            $this->command->info("Đã tạo nhân viên thử việc {$i}: {$user->name} - Phòng ban: {$department->name}");
        }

        $this->command->info('Đã tạo thành công 12 nhân viên thử việc!');
    }
}
