<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();
        
        if (!$user) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        // Kiểm tra role sử dụng normalizedRole() method
        $userRole = $user->normalizedRole();
        $allowedRoles = array_map(fn($r) => strtolower(trim($r)), $roles);
        
        if (!in_array($userRole, $allowedRoles)) {
            \Log::warning("RoleMiddleware blocked: user={$user->id}, role={$userRole}, allowed=" . json_encode($allowedRoles));
            abort(403, 'Không đủ quyền thao tác');
        }
        
        return $next($request);
    }
}

