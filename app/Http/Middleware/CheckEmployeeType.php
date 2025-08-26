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
        
        // Nếu là nhân viên mới, chuyển hướng đến trang thông báo
        if ($user->role === 'employee' && $user->employee_type === 'new') {
            return redirect()->route('employees.new.notice')->with('warning', 'Tài khoản của bạn đang chờ được phê duyệt. Vui lòng liên hệ quản trị viên.');
        }
        
        return $next($request);
    }
}
