<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class DepartmentPermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Admin có thể làm mọi thứ
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Manager chỉ có thể quản lý user cùng phòng ban
        if ($user->isManager()) {
            // Kiểm tra assignee_id (single user)
            $targetUserId = $request->input('assignee_id');
            if ($targetUserId) {
                $targetUser = User::find($targetUserId);
                if ($targetUser && $targetUser->department_id !== $user->department_id) {
                    abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
                }
            }
            
            // Kiểm tra assignee_ids (multi-user)
            $assigneeIds = $request->input('assignee_ids');
            if (is_array($assigneeIds)) {
                foreach ($assigneeIds as $assigneeId) {
                    $targetUser = User::find($assigneeId);
                    if ($targetUser && $targetUser->department_id !== $user->department_id) {
                        abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
                    }
                }
            }
            
            // Kiểm tra department_id (single department)
            $departmentId = $request->input('department_id');
            if ($departmentId && $departmentId !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể giao việc cho phòng ban của mình.');
            }
            
            // Kiểm tra department_ids (multi-department)
            $departmentIds = $request->input('department_ids');
            if (is_array($departmentIds)) {
                foreach ($departmentIds as $deptId) {
                    if ($deptId !== $user->department_id) {
                        abort(403, 'Bạn chỉ có thể giao việc cho phòng ban của mình.');
                    }
                }
            }
            
            // Kiểm tra user từ route parameter
            $routeUser = $request->route('user');
            if ($routeUser && $routeUser->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể quản lý nhân viên cùng phòng ban.');
            }
        }
        
        return $next($request);
    }
}
