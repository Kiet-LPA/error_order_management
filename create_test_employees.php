<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Department;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREATE TEST EMPLOYEES ===\n\n";

try {
    // Tạo employees cho 4 phòng ban
    $departments = Department::whereIn('name', ['Factory', 'HR', 'IT', 'Finance'])->get();
    
    foreach ($departments as $dept) {
        // Tạo 2 employees cho mỗi phòng ban
        for ($i = 1; $i <= 2; $i++) {
            $user = User::create([
                'name' => "Employee {$dept->name} {$i}",
                'email' => "employee-{$dept->name}-{$i}@example.com",
                'password' => bcrypt('password'),
                'role' => 'employee',
                'employee_type' => 'official',
                'account_status' => 'active',
                'department_id' => $dept->id,
            ]);
            
            // Gán phòng ban
            $user->departments()->attach([
                $dept->id => [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
            
            echo "✅ Tạo Employee: {$user->name} - {$dept->name}\n";
        }
    }
    
    echo "\n=== KẾT QUẢ ===\n";
    echo "✅ Đã tạo 8 employees cho 4 phòng ban\n";
    echo "✅ Mỗi phòng ban có 2 employees\n";
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
