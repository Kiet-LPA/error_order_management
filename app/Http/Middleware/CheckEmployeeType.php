<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmployeeType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Nếu user chưa đăng nhập, cho phép tiếp tục
        if (!$user) {
            return $next($request);
        }
        
        // Admin và Director luôn luôn active, không bị chặn
        if ($user->account_status === 'inactive' && !$user->isAdmin() && !$user->isDirector()) {
            // Cho phép truy cập notice page, logout và profile
            $allowedRoutes = [
                'employees.new.notice',
                'logout',
                'profile.edit',
                'profile.update'
            ];
            
            $currentRoute = $request->route() ? $request->route()->getName() : null;
            
            // Nếu không phải route được phép, chuyển hướng đến notice page
            if (!in_array($currentRoute, $allowedRoutes)) {
                return redirect()->route('employees.new.notice')->with('warning', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên để kích hoạt.');
            }
        }
        
        return $next($request);
    }
}
