<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        // Tạo department nếu chưa có
        $department = Department::firstOrCreate(
            ['name' => 'IT'],
            ['description' => 'Phòng IT']
        );

        // Tạo user test
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'role' => 'employee',
                'department_id' => $department->id
            ]
        );

        echo "Test user created: {$user->name}\n";
        echo "Department: {$department->name}\n";
        echo "Department ID: {$user->department_id}\n";
    }
}
