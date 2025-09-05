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
        
        
        // Kiểm tra role (case-insensitive và trim whitespace)
        $userRole = $user ? strtolower(trim($user->role)) : null;
        $allowedRoles = array_map('strtolower', $roles);
        
        if (!$user || !in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        return $next($request);
    }
}

