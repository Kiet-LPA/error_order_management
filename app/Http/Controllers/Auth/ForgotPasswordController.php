<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class ForgotPasswordController extends Controller
{
    /**
     * Hiển thị form quên mật khẩu
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Gửi link reset (kiểm tra tài khoản và lưu session)
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
        ], [
            'email_or_phone.required' => 'Vui lòng nhập email hoặc số điện thoại.',
        ]);

        $emailOrPhone = $request->email_or_phone;

        // Kiểm tra xem có phải email hay phone
        $isEmail = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            $user = User::where('email', $emailOrPhone)->first();
        } else {
            $user = User::where('phone', $emailOrPhone)->first();
        }

        if (!$user) {
            return back()->withErrors([
                'email_or_phone' => 'Không tìm thấy tài khoản với thông tin này.'
            ]);
        }

        // Lưu user_id vào session
        Session::put('reset_user_id', $user->id);
        Session::put('reset_user_email', $user->email);

        return redirect()->route('password.reset')->with('success', 'Vui lòng nhập mật khẩu mới cho tài khoản của bạn.');
    }

    /**
     * Hiển thị form đặt mật khẩu mới
     */
    public function showResetForm()
    {
        // Kiểm tra xem có session reset không
        if (!Session::has('reset_user_id')) {
            return redirect()->route('password.request')->withErrors([
                'email_or_phone' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thử lại.'
            ]);
        }

        return view('auth.reset-password');
    }

    /**
     * Cập nhật mật khẩu mới
     */
    public function resetPassword(Request $request)
    {
        // Kiểm tra xem có session reset không
        if (!Session::has('reset_user_id')) {
            return redirect()->route('password.request')->withErrors([
                'email_or_phone' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thử lại.'
            ]);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $userId = Session::get('reset_user_id');
        $user = User::find($userId);

        if (!$user) {
            Session::forget(['reset_user_id', 'reset_user_email']);
            return redirect()->route('password.request')->withErrors([
                'email_or_phone' => 'Tài khoản không tồn tại.'
            ]);
        }

        // Cập nhật mật khẩu mới
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Xóa session
        Session::forget(['reset_user_id', 'reset_user_email']);

        return redirect()->route('login')->with('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập với mật khẩu mới.');
    }
}
