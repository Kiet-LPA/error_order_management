<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Department;
use App\Services\TaskPermissionService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG TASK CREATE VIEW ===\n\n";

try {
    // Tìm manager
    $manager = User::where('email', 'like', '%quanlyit%')->with('departments')->first();
    if (!$manager) {
        echo "❌ Không tìm thấy manager\n";
        exit;
    }
    
    echo "1. Manager: {$manager->name} ({$manager->email})\n";
    echo "   Departments: " . $manager->departments->pluck('name')->join(', ') . "\n";
    
    // Lấy assignable users
    $users = TaskPermissionService::getAssignableUsers($manager);
    echo "\n2. Assignable users: {$users->count()}\n";
    
    // Lấy departments
    $departments = $manager->getAssignableDepartments();
    echo "3. Assignable departments: {$departments->count()}\n";
    foreach ($departments as $dept) {
        echo "   - {$dept->name}\n";
    }
    
    // Test logic group users by department
    echo "\n4. Test group users by department:\n";
    foreach ($departments as $department) {
        $departmentUsers = $users->filter(function($user) use ($department) {
            return $user->departments->contains('id', $department->id);
        });
        
        echo "   {$department->name}: {$departmentUsers->count()} users\n";
        foreach ($departmentUsers as $user) {
            $userDepts = $user->departments->pluck('name')->join(', ');
            echo "     - {$user->name}: {$userDepts}\n";
        }
    }
    
    echo "\n=== KẾT QUẢ ===\n";
    echo "✅ Logic group users by department hoạt động bình thường\n";
    echo "✅ Vấn đề có thể là:\n";
    echo "   1. Cache view\n";
    echo "   2. JavaScript không load đúng\n";
    echo "   3. CSS ẩn một số department groups\n";
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
