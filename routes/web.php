<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkReportController;
use App\Http\Controllers\TaskFollowerController;
use App\Http\Controllers\TaskApprovalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\CheckinController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Vào root thì chuyển sang kanban
Route::get('/', function () {
    return redirect()->route('kanban');
});

// Route test trang 404 (chỉ trong development)
Route::get('/test-404', function () {
    if (app()->environment('production')) {
        abort(404);
    }
    abort(404, 'Test trang 404 với con cá tra nhảy nhảy');
})->name('test.404');

// Route để chạy thủ công cập nhật overdue (chỉ admin)
Route::get('/admin/update-overdue', function () {
    if (!auth()->check() || !auth()->user()->isAdmin()) {
        abort(403);
    }
    
    \Artisan::call('tasks:update-overdue');
    $output = \Artisan::output();
    
    return response()->json([
        'success' => true,
        'message' => 'Đã cập nhật trạng thái overdue',
        'output' => $output
    ]);
})->name('admin.update-overdue');

// Route để serve avatar files
Route::get('/storage/avatars/{filename}', function ($filename) {
    $path = storage_path('app/public/avatars/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->name('avatar.serve');

// Các route yêu cầu đăng nhập (KHÔNG dùng verified)
Route::middleware(['auth', 'employee.type'])->group(function () {

    // Dashboard: đổ dữ liệu động (controller trả về view 'welcome')
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & Director: quản lý user
    Route::middleware('role:admin,director')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['show']);
        // Nếu có DepartmentController thì thêm ở đây
        // Route::resource('departments', DepartmentController::class);
    });


    // Route đơn giản cho tasks index - không dùng middleware group
    Route::get('/tasks', function() {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Không đủ quyền thao tác');
        }
        
        // Kiểm tra role thủ công
        $userRole = strtolower(trim($user->role));
        $allowedRoles = ['admin', 'director', 'manager'];
        
        if (!in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        // Nếu là Manager, kiểm tra department
        if ($userRole === 'manager' && !$user->department_id) {
            abort(403, 'Bạn chưa được phân phòng ban. Vui lòng liên hệ quản trị viên.');
        }
        
        // Gọi TaskController@index
        $controller = new \App\Http\Controllers\TaskController();
        return $controller->index(request());
    })->middleware('auth')->name('tasks.index');

    // Task creation routes - sử dụng hệ thống phân quyền mới
    Route::post('/tasks', [TaskController::class, 'store'])
        ->middleware('department.permission')
        ->name('tasks.store');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    
    // Task detail route - tất cả role có thể xem task detail
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('task-detail');
    
    // Subtasks routes - tất cả role có thể hoàn thành subtasks của mình
    Route::post('/tasks/{task}/subtasks/{subtask}/complete', [TaskController::class, 'completeSubtask'])->name('tasks.subtasks.complete');
    Route::patch('/tasks/{task}/subtasks/{subtask}/status', [TaskController::class, 'updateSubtaskStatus'])->name('tasks.subtasks.update-status');
    
    // Manager & Admin: Các route khác của Task
    Route::middleware(['role:admin,director,manager'])->group(function () {
        // Các route khác của resource
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        
        // Kanban Board - chỉ Admin/Director/Manager có thể cập nhật status (đã chuyển xuống dưới)
    });
    
    // Kanban Board - tất cả role có thể xem (nhưng chỉ Admin/Director/Manager có thể drag & drop)
    Route::get('/kanban', [TaskController::class, 'kanban'])->name('kanban');
    
    // Task Followers routes (Admin/Director/Manager only)
    Route::middleware('role:admin,director,manager')->group(function () {
        Route::post('/tasks/{task}/followers/add', [TaskFollowerController::class, 'add'])->name('tasks.followers.add');
        Route::delete('/tasks/{task}/followers/remove', [TaskFollowerController::class, 'remove'])->name('tasks.followers.remove');
        
        // Task Approvals routes (Admin/Director/Manager only)
        Route::get('/task-approvals', [TaskApprovalController::class, 'index'])->name('task-approvals.index');
        Route::get('/task-approvals/{approval}', [TaskApprovalController::class, 'show'])->name('task-approvals.show');
        Route::post('/task-approvals/{approval}/approve', [TaskApprovalController::class, 'approve'])->name('task-approvals.approve');
        Route::post('/task-approvals/{approval}/reject', [TaskApprovalController::class, 'reject'])->name('task-approvals.reject');
    });
    
    // Support Request routes
    Route::middleware('role:admin,director,manager,employee')->group(function () {
        Route::get('/support-requests', [SupportRequestController::class, 'index'])->name('support-requests.index');
        Route::get('/support-requests/create', [SupportRequestController::class, 'create'])->name('support-requests.create');
        Route::post('/support-requests', [SupportRequestController::class, 'store'])->name('support-requests.store');
        
        // Yêu cầu của tôi - Employee, Manager (phải đặt trước {supportRequest})
        Route::get('/support-requests/my-requests', [SupportRequestController::class, 'myRequests'])->name('support-requests.my-requests');
        
        // Yêu cầu phòng ban - Manager (phải đặt trước {supportRequest})
        Route::get('/support-requests/department-requests', [SupportRequestController::class, 'departmentRequests'])->name('support-requests.department-requests');
        
        Route::get('/support-requests/{supportRequest}', [SupportRequestController::class, 'show'])->name('support-requests.show');
        Route::post('/support-requests/{supportRequest}/comment', [SupportRequestController::class, 'comment'])->name('support-requests.comment');
    });

    // Admin & Director & Manager: Approve/Reject/Forward support requests
    Route::middleware('role:admin,director,manager')->group(function () {
        Route::get('/support-requests-quest-detail', [SupportRequestController::class, 'questDetail'])->name('support-requests.quest-detail');
        
        Route::post('/support-requests/{supportRequest}/approve', [SupportRequestController::class, 'approve'])->name('support-requests.approve');
        Route::post('/support-requests/{supportRequest}/reject', [SupportRequestController::class, 'reject'])->name('support-requests.reject');
        Route::post('/support-requests/{supportRequest}/forward', [SupportRequestController::class, 'forward'])->name('support-requests.forward');
        
        // Hoàn tác (undo) approve/reject
        Route::post('/support-requests/{supportRequest}/undo', [SupportRequestController::class, 'undoApprovalRejection'])->name('support-requests.undo');
    });

    // Admin & Director: Xóa support requests
    Route::middleware('role:admin,director')->group(function () {
        Route::delete('/support-requests/{supportRequest}', [SupportRequestController::class, 'destroy'])->name('support-requests.destroy');
    });

    // Employee: Hủy yêu cầu hỗ trợ (trong 3 giờ)
    Route::middleware('role:employee')->group(function () {
        Route::post('/support-requests/{supportRequest}/cancel', [SupportRequestController::class, 'cancelRequest'])->name('support-requests.cancel');
    });


    // Forward task
    Route::get('/tasks/{task}/forward', [TaskController::class, 'showForwardForm'])
        ->middleware(['role:admin,director,manager'])
        ->name('tasks.forward.form');
    Route::post('/tasks/{task}/forward', [TaskController::class, 'forward'])
        ->middleware(['role:admin,director,manager'])
        ->name('tasks.forward');

    // Alias để khớp link của giao diện cũ (mọi role đều có thể xem chi tiết)
    Route::get('/task-detail/{task}', [TaskController::class, 'show'])->name('task-detail');
    // Alias cho form tạo (trùng với tasks.create nhưng để khớp UI cũ)
    Route::get('/create-task', [TaskController::class, 'create'])
        ->middleware(['role:admin,director,manager'])
        ->name('create-task');
    // Cập nhật trạng thái & xem lịch sử: cho tất cả role đã đăng nhập, quyền kiểm tra trong controller
    Route::get('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::post('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::get('/tasks/{task}/history', [TaskController::class, 'history'])->name('tasks.history');
    Route::post('/tasks/{task}/remove-file', [TaskController::class, 'removeFile'])->name('tasks.removeFile');
    Route::post('/tasks/{task}/undo-completion', [TaskController::class, 'undoCompletion'])->name('tasks.undo-completion');
    
    // Employee/Manager/Admin/Director: trang "my tasks" & comment trên task
    Route::middleware(['role:employee,manager,admin,director', 'employee.type'])->group(function () {
        Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.mine');
    });

    // Task Followers routes (cho tất cả users)
    Route::middleware(['auth', 'employee.type'])->group(function () {
        Route::post('/tasks/{task}/followers/follow', [TaskFollowerController::class, 'follow'])->name('tasks.followers.follow');
        Route::delete('/tasks/{task}/followers/unfollow', [TaskFollowerController::class, 'unfollow'])->name('tasks.followers.unfollow');
    });

    // Notification routes (cho tất cả users)
    Route::middleware(['auth', 'employee.type'])->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
        Route::post('/notifications/delete', [NotificationController::class, 'delete'])->name('notifications.delete');
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    });

    // Comment routes
    Route::middleware(['auth', 'employee.type'])->group(function () {
        Route::post('/tasks/{task}/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
        Route::put('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('/comments/{comment}/reactions', [App\Http\Controllers\CommentController::class, 'addReaction'])->name('comments.reactions');
        Route::delete('/comment-attachments/{attachment}', [App\Http\Controllers\CommentController::class, 'deleteAttachment'])->name('comment.attachments.delete');
    });

    // Báo cáo tổng quan (thường cho manager & admin & director)
    Route::middleware('role:admin,director,manager')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Báo cáo công việc - cho tất cả role
    Route::middleware(['role:employee,manager,admin,director', 'employee.type'])->group(function () {
        Route::get('/work-reports', [WorkReportController::class, 'index'])->name('work-reports.index');
        Route::get('/work-reports/select-date', [WorkReportController::class, 'selectDate'])->name('work-reports.select-date');
        Route::get('/work-reports/create', [WorkReportController::class, 'create'])->name('work-reports.create');
        Route::post('/work-reports', [WorkReportController::class, 'store'])->name('work-reports.store');
        
        // API routes - phải đặt trước route {workReport}
        Route::get('/work-reports/week', [WorkReportController::class, 'showWeek'])->name('work-reports.show-week');
        Route::get('/work-reports/months', [WorkReportController::class, 'getMonths'])->name('work-reports.months');
        Route::get('/work-reports/weeks', [WorkReportController::class, 'getWeeks'])->name('work-reports.weeks');
        Route::get('/work-reports/employees', [WorkReportController::class, 'getEmployeesByDepartment'])->name('work-reports.employees');
        Route::get('/work-reports/employee-reports', [WorkReportController::class, 'getEmployeeReports'])->name('work-reports.employee-reports');
        Route::get('/work-reports/week-from-date', [WorkReportController::class, 'getWeekFromDate'])->name('work-reports.week-from-date');
        
        // Resource routes - phải đặt sau API routes
        Route::get('/work-reports/{workReport}', [WorkReportController::class, 'show'])->name('work-reports.show');
        Route::get('/work-reports/{workReport}/edit', [WorkReportController::class, 'edit'])->name('work-reports.edit');
        Route::put('/work-reports/{workReport}', [WorkReportController::class, 'update'])->name('work-reports.update');
        Route::delete('/work-reports/{workReport}', [WorkReportController::class, 'destroy'])->name('work-reports.destroy');
        
        // Routes cho theo dõi hoạt động
        Route::get('/work-reports/current-week', [WorkReportController::class, 'currentWeek'])->name('work-reports.current-week');
        Route::get('/work-reports/current-month', [WorkReportController::class, 'currentMonth'])->name('work-reports.current-month');
        Route::get('/work-reports/my-activity', [WorkReportController::class, 'myActivity'])->name('work-reports.my-activity');
        Route::post('/work-reports/mark-as-read', [WorkReportController::class, 'markAsRead'])->name('work-reports.mark-as-read');
        Route::post('/work-reports/reject', [WorkReportController::class, 'reject'])->name('work-reports.reject');
    });
});

// Quản lý nhân viên mới
Route::middleware(['auth', 'role:admin,director'])->group(function () {
    Route::get('/employees/new', [App\Http\Controllers\EmployeeController::class, 'newEmployeesIndex'])->name('employees.new.index');
    Route::get('/employees/new/create', [App\Http\Controllers\EmployeeController::class, 'newEmployeesCreate'])->name('employees.new.create');
    Route::post('/employees/new', [App\Http\Controllers\EmployeeController::class, 'newEmployeesStore'])->name('employees.new.store');
    Route::get('/employees/new/{user}/edit', [App\Http\Controllers\EmployeeController::class, 'newEmployeesEdit'])->name('employees.new.edit');
    Route::put('/employees/new/{user}', [App\Http\Controllers\EmployeeController::class, 'newEmployeesUpdate'])->name('employees.new.update');
    Route::delete('/employees/new/{employee}', [App\Http\Controllers\EmployeeController::class, 'newEmployeesDestroy'])->name('employees.new.destroy');
    Route::post('/employees/{user}/convert', [App\Http\Controllers\EmployeeController::class, 'convertToOfficial'])->name('employees.convert');
});

// Trang thông báo cho nhân viên mới
Route::middleware(['auth'])->group(function () {
    Route::get('/employees/new/notice', [App\Http\Controllers\EmployeeController::class, 'newEmployeeNotice'])->name('employees.new.notice');
});


// Quản lý lương
Route::middleware(['auth', 'role:admin,director'])->group(function () {
    Route::get('/employees/salary', [App\Http\Controllers\EmployeeController::class, 'salaryIndex'])->name('employees.salary.index');
    Route::get('/employees/salary/{salary}/edit', [App\Http\Controllers\EmployeeController::class, 'salaryEdit'])->name('employees.salary.edit');
    Route::put('/employees/salary/{salary}', [App\Http\Controllers\EmployeeController::class, 'salaryUpdate'])->name('employees.salary.update');
});

// Profile routes
Route::middleware(['auth', 'employee.type'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    // Avatar routes
    Route::post('/avatar/upload', [App\Http\Controllers\AvatarController::class, 'upload'])->name('avatar.upload');
    Route::delete('/avatar/remove', [App\Http\Controllers\AvatarController::class, 'remove'])->name('avatar.remove');
    Route::post('/avatar/upload/{userId}', [App\Http\Controllers\AvatarController::class, 'uploadForUser'])->name('avatar.upload.user');
    Route::delete('/avatar/remove/{userId}', [App\Http\Controllers\AvatarController::class, 'remove'])->name('avatar.remove.user');
});

// Chỉ require auth.php nếu đã cài Breeze/Jetstream (tránh lỗi file không tồn tại)
if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}

// Approval System Routes
Route::middleware(['auth'])->group(function () {
    // Approval Requests
    Route::get('/approval', [App\Http\Controllers\ApprovalController::class, 'index'])->name('approval.index');
    Route::get('/approval/create', [App\Http\Controllers\ApprovalController::class, 'create'])->name('approval.create');
    Route::get('/approval/create/{formType}', [App\Http\Controllers\ApprovalController::class, 'create'])->name('approval.create.type');
    
    // API routes for approval system
    Route::get('/api/users/approval-eligible', function() {
        $users = \App\Models\User::where('role', 'manager')
            ->select('id', 'name', 'role')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    });
    Route::post('/approval', [App\Http\Controllers\ApprovalController::class, 'store'])->name('approval.store');
    Route::get('/approval/{id}', [App\Http\Controllers\ApprovalController::class, 'show'])->name('approval.show');
    Route::get('/approval/{id}/edit', [App\Http\Controllers\ApprovalController::class, 'edit'])->name('approval.edit');
    Route::put('/approval/{id}', [App\Http\Controllers\ApprovalController::class, 'update'])->name('approval.update');
    Route::post('/approval/{id}/approve', [App\Http\Controllers\ApprovalController::class, 'approve'])->name('approval.approve');
    Route::post('/approval/{id}/reject', [App\Http\Controllers\ApprovalController::class, 'reject'])->name('approval.reject');
    Route::delete('/approval/{id}/cancel', [App\Http\Controllers\ApprovalController::class, 'cancel'])->name('approval.cancel');
    
    // Forward Requests
    Route::post('/approval/{id}/forward', [App\Http\Controllers\ApprovalController::class, 'forward'])->name('approval.forward');
    
    // Status Management
    Route::patch('/approval/{id}/discussion-status', [App\Http\Controllers\StatusController::class, 'updateDiscussionStatus'])->name('approval.update-discussion-status');
    Route::patch('/approval/{id}/edit-status', [App\Http\Controllers\StatusController::class, 'updateEditStatus'])->name('approval.update-edit-status');
    
    // PDF Export
    Route::get('/approval/{id}/preview', [App\Http\Controllers\PDFController::class, 'preview'])->name('approval.preview');
    Route::get('/approval/{id}/pdf', [App\Http\Controllers\PDFController::class, 'generatePDF'])->name('approval.pdf');
    Route::get('/approval/{id}/print', [App\Http\Controllers\PDFController::class, 'print'])->name('approval.print');
    
    // Comments
    Route::post('/approval/{id}/comment', [App\Http\Controllers\CommentController::class, 'store'])->name('approval.comment');
    
    // Autocomplete suggestions
    Route::get('/approval/suggestions/items', [App\Http\Controllers\ApprovalController::class, 'getItemSuggestions'])->name('approval.item-suggestions');
    
    // Get managers by department
    Route::get('/api/managers/{departmentId}', [App\Http\Controllers\ApprovalController::class, 'getManagersByDepartment'])->name('api.managers.by-department');

// Approval actions
Route::post('/approval/{id}/approve', [App\Http\Controllers\ApprovalController::class, 'approve'])->name('approval.approve');
Route::post('/approval/{id}/reject', [App\Http\Controllers\ApprovalController::class, 'reject'])->name('approval.reject');
Route::post('/approval/bulk-approve', [App\Http\Controllers\ApprovalController::class, 'bulkApprove'])->name('approval.bulk-approve');
Route::post('/approval/bulk-reject', [App\Http\Controllers\ApprovalController::class, 'bulkReject'])->name('approval.bulk-reject');
});

// Checkin routes
Route::middleware(['auth'])->group(function () {
    Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin.index');
    Route::post('/checkin', [CheckinController::class, 'checkin'])->name('checkin.checkin');
    Route::get('/checkin/history', [CheckinController::class, 'history'])->name('checkin.history');
    Route::get('/checkin/gps-help', function() {
        return view('checkin.gps-help');
    })->name('checkin.gps-help');
});

// Admin/Director/Manager Checkin Management routes
Route::middleware(['auth', 'role:admin,director,manager'])->prefix('admin/checkin')->name('admin.checkin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminCheckinController::class, 'index'])->name('index');
    Route::get('/manage', [\App\Http\Controllers\AdminCheckinController::class, 'manage'])->name('manage');
    Route::get('/gps-requests', [\App\Http\Controllers\AdminCheckinController::class, 'gpsRequests'])->name('gps-requests');
    Route::post('/gps-requests/{gpsRequest}/approve', [\App\Http\Controllers\AdminCheckinController::class, 'approveGpsRequest'])->name('approve-gps');
    Route::post('/fix-attendance', [\App\Http\Controllers\AdminCheckinController::class, 'fixAttendance'])->name('fix-attendance');
    Route::delete('/{checkin}', [\App\Http\Controllers\AdminCheckinController::class, 'deleteCheckin'])->name('delete');
    Route::get('/reports', [\App\Http\Controllers\AdminCheckinController::class, 'reports'])->name('reports');
});


