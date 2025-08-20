<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Vào root thì chuyển sang dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Các route yêu cầu đăng nhập (KHÔNG dùng verified)
Route::middleware(['auth'])->group(function () {

    // Dashboard: đổ dữ liệu động (controller trả về view 'welcome')
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Chỉ Admin: quản lý user
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['show']);
        // Nếu có DepartmentController thì thêm ở đây
        // Route::resource('departments', DepartmentController::class);
    });

    // Manager & Admin: CRUD Task (tránh trùng, bỏ 'show' vì dùng alias riêng)
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('tasks', TaskController::class)->except(['show']);
    });
    
    // Lưu task (nút "Giao việc") - áp dụng middleware kiểm tra phòng ban
    Route::post('/tasks', [TaskController::class, 'store'])
        ->middleware('role:admin,manager')
        ->name('tasks.store');

    // Alias để khớp link của giao diện cũ (mọi role đều có thể xem chi tiết)
    Route::get('/task-detail/{task}', [TaskController::class, 'show'])->name('task-detail');
    // Alias cho form tạo (trùng với tasks.create nhưng để khớp UI cũ)
    Route::get('/create-task', [TaskController::class, 'create'])->name('create-task');
    // Cập nhật trạng thái & xem lịch sử: cho tất cả role đã đăng nhập, quyền kiểm tra trong controller
    Route::get('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::get('/tasks/{task}/history', [TaskController::class, 'history'])->name('tasks.history');
    Route::post('/tasks/{task}/remove-file', [TaskController::class, 'removeFile'])->name('tasks.removeFile');
    
    // Employee/Manager/Admin: trang "my tasks" & comment trên task
    Route::middleware('role:employee,manager,admin')->group(function () {
        Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.mine');
        Route::post('/tasks/{task}/comment', [TaskController::class, 'comment'])->name('tasks.comment');
    });

    // Báo cáo tổng quan (thường cho manager & admin)
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
});

// Quản lý nhân viên mới
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/employees/new', [App\Http\Controllers\EmployeeController::class, 'newEmployeesIndex'])->name('employees.new.index');
    Route::get('/employees/new/create', [App\Http\Controllers\EmployeeController::class, 'newEmployeesCreate'])->name('employees.new.create');
    Route::post('/employees/new', [App\Http\Controllers\EmployeeController::class, 'newEmployeesStore'])->name('employees.new.store');
    Route::post('/employees/{user}/convert', [App\Http\Controllers\EmployeeController::class, 'convertToOfficial'])->name('employees.convert');
});

// Quản lý lương
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/employees/salary', [App\Http\Controllers\EmployeeController::class, 'salaryIndex'])->name('employees.salary.index');
    Route::get('/employees/salary/{salary}/edit', [App\Http\Controllers\EmployeeController::class, 'salaryEdit'])->name('employees.salary.edit');
    Route::put('/employees/salary/{salary}', [App\Http\Controllers\EmployeeController::class, 'salaryUpdate'])->name('employees.salary.update');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Chỉ require auth.php nếu đã cài Breeze/Jetstream (tránh lỗi file không tồn tại)
if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}


