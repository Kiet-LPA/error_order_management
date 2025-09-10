<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Department;
use App\Services\TaskPermissionService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST MANAGER ASSIGNABLE USERS ===\n\n";

try {
    // Tìm user quanlyit1@gmail.com
    $manager = User::where('email', 'like', '%quanlyit%')->with('departments')->first();
    if (!$manager) {
        echo "❌ Không tìm thấy user quanlyit\n";
        // Tìm tất cả manager
        $managers = User::where('role', 'manager')->get();
        echo "Danh sách managers:\n";
        foreach ($managers as $m) {
            echo "  - {$m->email} - {$m->name}\n";
        }
        exit;
    }
    
    echo "1. Manager info:\n";
    echo "   ✓ ID: {$manager->id}\n";
    echo "   ✓ Tên: {$manager->name}\n";
    echo "   ✓ Email: {$manager->email}\n";
    echo "   ✓ Role: {$manager->role}\n";
    echo "   ✓ Departments: " . $manager->departments->pluck('name')->join(', ') . "\n";
    
    // Gán 4 phòng ban cho manager
    $departments = Department::whereIn('name', ['Factory', 'HR', 'IT', 'Finance'])->get();
    $manager->departments()->detach();
    $departmentsToAttach = [];
    foreach ($departments as $dept) {
        $departmentsToAttach[$dept->id] = [
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    $manager->departments()->attach($departmentsToAttach);
    
    // Reload manager
    $manager->refresh();
    $manager->load('departments');
    
    echo "\n2. Manager sau khi gán 4 phòng ban:\n";
    echo "   ✓ Departments: " . $manager->departments->pluck('name')->join(', ') . "\n";
    
    // Test getAssignableUsers
    echo "\n3. Test getAssignableUsers:\n";
    $assignableUsers = TaskPermissionService::getAssignableUsers($manager);
    echo "   ✓ Số users có thể assign: {$assignableUsers->count()}\n";
    
    foreach ($assignableUsers as $user) {
        $userDepartments = $user->departments->pluck('name')->join(', ');
        echo "   - {$user->name} (ID: {$user->id}): {$userDepartments}\n";
    }
    
    // Test logic query
    echo "\n4. Test query logic:\n";
    $managerDepartmentIds = $manager->departments->pluck('id')->toArray();
    echo "   ✓ Manager department IDs: " . implode(', ', $managerDepartmentIds) . "\n";
    
    $employees = User::where('role', 'employee')
        ->where('id', '!=', $manager->id)
        ->whereHas('departments', function($query) use ($managerDepartmentIds) {
            $query->whereIn('department_id', $managerDepartmentIds);
        })
        ->with('departments')
        ->get();
    
    echo "   ✓ Employees từ query: {$employees->count()}\n";
    foreach ($employees as $emp) {
        $empDepartments = $emp->departments->pluck('name')->join(', ');
        echo "     - {$emp->name}: {$empDepartments}\n";
    }
    
    echo "\n=== KẾT QUẢ ===\n";
    if ($assignableUsers->count() > 0) {
        echo "✅ getAssignableUsers hoạt động bình thường\n";
    } else {
        echo "❌ getAssignableUsers không trả về user nào\n";
        echo "❌ Có thể vấn đề là:\n";
        echo "   1. Không có Employee nào thuộc 4 phòng ban này\n";
        echo "   2. Logic query có vấn đề\n";
    }
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
