<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller

{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin có thể xem tất cả users
            $users = User::with('department')->latest()->paginate(15);
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xem users cùng phòng ban
            $users = User::with('department')
                        ->where('department_id', $user->department_id)
                        ->latest()
                        ->paginate(15);
        } else {
            abort(403, 'Bạn không có quyền xem danh sách người dùng.');
        }
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin có thể tạo user cho bất kỳ phòng ban nào
            $departments = Department::orderBy('name')->get();
        } elseif ($user->isManager()) {
            // Manager chỉ có thể tạo user cho phòng ban của mình
            $departments = Department::where('id', $user->department_id)->get();
        } else {
            abort(403, 'Bạn không có quyền tạo người dùng.');
        }
        
        return view('admin.users.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền tạo user
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền tạo người dùng.');
        }
        
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>['nullable','email','unique:users,email'],
            'phone'=>['nullable','string','max:20','unique:users,phone'],
            'password'=>'required|min:8|confirmed',
            'role'=>'required|in:admin,manager,employee',
            'department_id'=>'nullable|exists:departments,id',
        ]);
        
        // Kiểm tra ít nhất phải có email hoặc số điện thoại
        if (empty($data['email']) && empty($data['phone'])) {
            return back()->withErrors(['email'=>'Phải có ít nhất email hoặc số điện thoại.'])->withInput();
        }
        
        // Bắt buộc department cho manager/employee
        if (in_array($data['role'], ['manager','employee']) && empty($data['department_id'])) {
            return back()->withErrors(['department_id'=>'Bắt buộc chọn phòng ban.'])->withInput();
        }
        
        // Manager chỉ có thể tạo user cho phòng ban của mình
        if ($user->isManager() && $data['department_id'] !== $user->department_id) {
            abort(403, 'Bạn chỉ có thể tạo người dùng cho phòng ban của mình.');
        }
        
        // Manager không thể tạo admin
        if ($user->isManager() && $data['role'] === 'admin') {
            abort(403, 'Bạn không có quyền tạo tài khoản admin.');
        }
        
        $data['password'] = bcrypt($data['password']);
        User::create($data);
        return redirect()->route('users.index')->with('success','Tạo người dùng thành công.');
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        // Kiểm tra quyền chỉnh sửa user
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Bạn không có quyền chỉnh sửa người dùng.');
        }
        
        // Manager chỉ có thể chỉnh sửa user cùng phòng ban
        if ($currentUser->isManager() && $user->department_id !== $currentUser->department_id) {
            abort(403, 'Bạn chỉ có thể chỉnh sửa người dùng cùng phòng ban.');
        }
        
        if ($currentUser->isAdmin()) {
            // Admin có thể chỉnh sửa user cho bất kỳ phòng ban nào
            $departments = Department::orderBy('name')->get();
        } else {
            // Manager chỉ có thể chỉnh sửa user cho phòng ban của mình
            $departments = Department::where('id', $currentUser->department_id)->get();
        }
        
        return view('admin.users.edit', compact('user','departments'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Kiểm tra quyền cập nhật user
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Bạn không có quyền cập nhật người dùng.');
        }
        
        // Manager chỉ có thể cập nhật user cùng phòng ban
        if ($currentUser->isManager() && $user->department_id !== $currentUser->department_id) {
            abort(403, 'Bạn chỉ có thể cập nhật người dùng cùng phòng ban.');
        }
        
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>['nullable','email', Rule::unique('users','email')->ignore($user->id)],
            'phone'=>['nullable','string','max:20', Rule::unique('users','phone')->ignore($user->id)],
            'password'=>'nullable|min:8|confirmed',
            'role'=>'required|in:admin,manager,employee',
            'department_id'=>'nullable|exists:departments,id',
        ]);
        
        // Kiểm tra ít nhất phải có email hoặc số điện thoại
        if (empty($data['email']) && empty($data['phone'])) {
            return back()->withErrors(['email'=>'Phải có ít nhất email hoặc số điện thoại.'])->withInput();
        }
        
        if (in_array($data['role'], ['manager','employee']) && empty($data['department_id'])) {
            return back()->withErrors(['department_id'=>'Bắt buộc chọn phòng ban.'])->withInput();
        }
        
        // Manager chỉ có thể cập nhật user cho phòng ban của mình
        if ($currentUser->isManager() && $data['department_id'] !== $currentUser->department_id) {
            abort(403, 'Bạn chỉ có thể cập nhật người dùng cho phòng ban của mình.');
        }
        
        // Manager không thể tạo admin
        if ($currentUser->isManager() && $data['role'] === 'admin') {
            abort(403, 'Bạn không có quyền tạo tài khoản admin.');
        }
        
        if (!empty($data['password'])) $data['password'] = bcrypt($data['password']); else unset($data['password']);
        $user->update($data);
        return redirect()->route('users.index')->with('success','Cập nhật thành công.');
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        // Kiểm tra quyền xóa user
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Bạn không có quyền xóa người dùng.');
        }
        
        // Manager chỉ có thể xóa user cùng phòng ban
        if ($currentUser->isManager() && $user->department_id !== $currentUser->department_id) {
            abort(403, 'Bạn chỉ có thể xóa người dùng cùng phòng ban.');
        }
        
        // Không thể xóa chính mình
        if ($currentUser->id === $user->id) {
            abort(403, 'Bạn không thể xóa tài khoản của chính mình.');
        }
        
        $user->delete();
        return back()->with('success','Đã xóa.');
    }
}
