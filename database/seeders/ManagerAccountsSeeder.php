<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class ManagerAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách phòng ban với tên ngắn gọn
        $departments = [
            'IT' => 'IT',
            'HR' => 'HR', 
            'Sales' => 'Sales',
            'Production' => 'Production',
            'Marketing' => 'Marketing',
            'Finance' => 'Finance',
            'Operations' => 'Operations',
            'Customer Service' => 'CS',
            'Research & Development' => 'R&D',
            'Legal' => 'Legal'
        ];

        foreach ($departments as $deptName => $deptCode) {
            // Tìm hoặc tạo phòng ban
            $department = Department::firstOrCreate(['name' => $deptName]);
            
            // Tạo 2 tài khoản quản lý cho mỗi phòng ban
            for ($i = 1; $i <= 2; $i++) {
                $email = 'quanly' . $deptCode . $i . '@gmail.com';
                $name = 'Quản lý ' . $deptName . ' ' . $i;
                
                // Mật khẩu là phần trước @gmail.com
                $password = 'quanly' . $deptCode . $i;
                
                // Kiểm tra xem tài khoản đã tồn tại chưa
                $existingUser = User::where('email', $email)->first();
                
                if (!$existingUser) {
                    User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'role' => 'manager',
                        'department_id' => $department->id,
                        'email_verified_at' => now(),
                    ]);
                    
                    $this->command->info("Đã tạo tài khoản: {$name} ({$email}) - Mật khẩu: {$password}");
                } else {
                    // Cập nhật mật khẩu nếu tài khoản đã tồn tại
                    $existingUser->update([
                        'password' => Hash::make($password)
                    ]);
                    $this->command->info("Đã cập nhật mật khẩu cho: {$name} ({$email}) - Mật khẩu mới: {$password}");
                }
            }
        }
        
        $this->command->info('Hoàn thành tạo/cập nhật tài khoản quản lý cho tất cả phòng ban!');
    }
}
