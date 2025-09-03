<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\UserDepartment;

class DirectorAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();
        
        // Tạo Director mặc định quản lý TẤT CẢ phòng ban
        $defaultDirector = User::updateOrCreate(
            ['email' => 'director.all@gmail.com'],
            [
                'name' => 'Director Tổng Quản Lý',
                'password' => Hash::make('director.all'),
                'role' => 'director',
                'department_id' => $departments->first()->id, // Chỉ để không bị lỗi foreign key
                'email_verified_at' => now(),
            ]
        );
        
        echo "Created Default Director: {$defaultDirector->name} (manages ALL departments)\n";
        
        // Tạo Director cho mỗi phòng ban cụ thể
        foreach ($departments as $department) {
            $director = User::updateOrCreate(
                ['email' => 'director' . $department->code . '@gmail.com'],
                [
                    'name' => 'Director ' . $department->name,
                    'password' => Hash::make('director' . $department->code),
                    'role' => 'director',
                    'department_id' => $department->id,
                    'email_verified_at' => now(),
                ]
            );
            
            // Tạo relationship trong user_departments cho phòng ban cụ thể
            UserDepartment::updateOrCreate(
                [
                    'user_id' => $director->id,
                    'department_id' => $department->id
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            echo "Created Department Director: {$director->name} for {$department->name}\n";
        }
        
        // Tạo một số Director quản lý nhiều phòng ban cụ thể
        $multiDepartmentDirectors = [
            [
                'name' => 'Director Operations',
                'email' => 'director.operations@gmail.com',
                'password' => 'director.operations',
                'departments' => ['Production', 'Factory', 'Operations']
            ],
            [
                'name' => 'Director Business',
                'email' => 'director.business@gmail.com',
                'password' => 'director.business',
                'departments' => ['Sales', 'Marketing', 'Customer Service']
            ],
            [
                'name' => 'Director Support',
                'email' => 'director.support@gmail.com',
                'password' => 'director.support',
                'departments' => ['IT', 'HR', 'Finance']
            ]
        ];
        
        foreach ($multiDepartmentDirectors as $directorData) {
            $director = User::updateOrCreate(
                ['email' => $directorData['email']],
                [
                    'name' => $directorData['name'],
                    'password' => Hash::make($directorData['password']),
                    'role' => 'director',
                    'department_id' => Department::where('name', $directorData['departments'][0])->first()->id,
                    'email_verified_at' => now(),
                ]
            );
            
            // Tạo relationship cho tất cả phòng ban được quản lý
            foreach ($directorData['departments'] as $deptName) {
                $department = Department::where('name', $deptName)->first();
                if ($department) {
                    UserDepartment::updateOrCreate(
                        [
                            'user_id' => $director->id,
                            'department_id' => $department->id
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
            
            echo "Created Multi-Department Director: {$director->name}\n";
        }
        
        echo "\n=== DIRECTOR ACCOUNTS SUMMARY ===\n";
        echo "1. Director mặc định: director.all@gmail.com / director.all (Quản lý TẤT CẢ phòng ban)\n";
        echo "2. Director theo phòng ban: directorIT@gmail.com / directorIT, directorMarketing@gmail.com / directorMarketing, etc.\n";
        echo "3. Director đa phòng ban: director.operations@gmail.com / director.operations, director.business@gmail.com / director.business, director.support@gmail.com / director.support\n";
        echo "================================\n";
    }
}
