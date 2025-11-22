<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Services\TaskPermissionService;

class DepartmentPermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Nếu user chưa đăng nhập, cho phép tiếp tục (auth middleware sẽ xử lý)
        if (!$user) {
            return $next($request);
        }
        
        // Admin và Director có thể làm mọi thứ
        if ($user->isAdmin() || $user->isDirector()) {
            return $next($request);
        }
        
        // Employee không cần kiểm tra middleware này
        if ($user->isEmployee()) {
            return $next($request);
        }
        
        // Manager: sử dụng TaskPermissionService để validate
        if ($user->isManager()) {
            $data = $request->all();
            $permissionErrors = TaskPermissionService::validateTaskAssignment($user, $data);
            
            if (!empty($permissionErrors)) {
                // Log để debug
                \Log::warning("Manager {$user->id} ({$user->name}) permission denied: " . json_encode($permissionErrors));
                abort(403, 'Bạn không có quyền thực hiện thao tác này.');
            }
        }
        
        return $next($request);
    }
}
