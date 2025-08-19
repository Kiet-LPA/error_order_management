<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
            $targetUserId = $request->input('assignee_id') ?? $request->route('user')?->id;
            
            if ($targetUserId) {
                $targetUser = \App\Models\User::find($targetUserId);
                
                if ($targetUser && $targetUser->department_id !== $user->department_id) {
                    abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
                }
            }
        }
        
        return $next($request);
    }
}
