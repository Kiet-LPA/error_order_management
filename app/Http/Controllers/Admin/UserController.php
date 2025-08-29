<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền xem danh sách người dùng.');
        }
        
        // Base query
        $query = User::with('department')->where('employee_type', 'official');
        
        // Manager chỉ có thể xem users cùng phòng ban
        if ($user->isManager()) {
            $query->where('department_id', $user->department_id);
        }
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->search($search);
        }
        
        // Department filter
        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }
        
        // Role filter
        if ($request->filled('role')) {
            $query->byRole($request->role);
        }
        
        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSortFields = ['name', 'email', 'role', 'created_at', 'department_id'];
        if (in_array($sortField, $allowedSortFields)) {
            if ($sortField === 'department_id') {
                $query->join('departments', 'users.department_id', '=', 'departments.id')
                      ->orderBy('departments.name', $sortDirection)
                      ->select('users.*');
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->latest();
        }
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [10, 15, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }
        
        $users = $query->paginate($perPage);
        
        // Statistics
        $stats = [
            'total' => User::where('employee_type', 'official')->count(),
            'admin' => User::where('employee_type', 'official')->where('role', 'admin')->count(),
            'manager' => User::where('employee_type', 'official')->where('role', 'manager')->count(),
            'employee' => User::where('employee_type', 'official')->where('role', 'employee')->count(),
        ];
        
        // Manager chỉ thấy stats của phòng ban mình
        if ($user->isManager()) {
            $stats = [
                'total' => User::where('employee_type', 'official')->where('department_id', $user->department_id)->count(),
                'admin' => User::where('employee_type', 'official')->where('department_id', $user->department_id)->where('role', 'admin')->count(),
                'manager' => User::where('employee_type', 'official')->where('department_id', $user->department_id)->where('role', 'manager')->count(),
                'employee' => User::where('employee_type', 'official')->where('department_id', $user->department_id)->where('role', 'employee')->count(),
            ];
        }
        
        // Get departments for filter
        $departments = Department::orderBy('name')->get();
        
        return view('employees.index', compact('users', 'stats', 'departments'));
    }

    public function create()
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền tạo người dùng.');
        }
        
        if ($user->isAdmin()) {
            // Admin có thể tạo user cho bất kỳ phòng ban nào
            $departments = Department::orderBy('name')->get();
        } else {
            // Manager chỉ có thể tạo user cho phòng ban của mình
            $departments = Department::where('id', $user->department_id)->get();
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
            'position'=>'nullable|string|max:255',
            'social_insurance_number'=>'nullable|string|max:50',
            'health_insurance_number'=>'nullable|string|max:50',
            'personal_identification_number'=>'nullable|string|max:50',
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
        
        // Chỉ xử lý nhân viên chính thức
        if ($user->employee_type === 'new') {
            abort(404, 'Vui lòng sử dụng form chỉnh sửa nhân viên mới.');
        }
        
        $departments = Department::orderBy('name')->get();
        return view('admin.users.edit-official', compact('user', 'departments'));
    }

    public function show(User $user)
    {
        $currentUser = auth()->user();
        
        // Kiểm tra quyền xem user
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Bạn không có quyền xem thông tin người dùng.');
        }
        
        // Manager chỉ có thể xem user cùng phòng ban
        if ($currentUser->isManager() && $user->department_id !== $currentUser->department_id) {
            abort(403, 'Bạn chỉ có thể xem thông tin người dùng cùng phòng ban.');
        }
        
        // Load các relationship cần thiết
        $user->load(['department', 'contracts.images', 'salary']);
        
        return view('admin.users.show', compact('user'));
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
        
        // Chỉ xử lý nhân viên chính thức
        if ($user->employee_type === 'new') {
            abort(404, 'Vui lòng sử dụng form chỉnh sửa nhân viên mới.');
        }
        
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>['nullable','email', Rule::unique('users','email')->ignore($user->id)],
            'phone'=>['nullable','string','max:20', Rule::unique('users','phone')->ignore($user->id)],
            'password'=>'nullable|min:8|confirmed',
            'role'=>'required|in:admin,manager,employee',
            'department_id'=>'nullable|exists:departments,id',
            'position'=>'nullable|string|max:255',
            'social_insurance_number'=>'nullable|string|max:50',
            'health_insurance_number'=>'nullable|string|max:50',
            'personal_identification_number'=>'nullable|string|max:50',
            'contract_images.*'=>'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Thông tin hợp đồng
            'contract_salary'=>'nullable|numeric|min:0',
            'contract_period'=>'nullable|integer|min:1|max:60',
            'contract_start_date'=>'nullable|date',
            'contract_status'=>'nullable|in:active,completed,terminated',
        ]);
        
        // Kiểm tra ít nhất phải có email hoặc số điện thoại cho nhân viên chính thức
        if (empty($data['email']) && empty($data['phone'])) {
            return back()->withErrors(['email'=>'Phải có ít nhất email hoặc số điện thoại.'])->withInput();
        }
        
        // Kiểm tra thông tin hợp đồng
        if ($request->filled('contract_start_date') && $request->filled('contract_period')) {
            $startDate = \Carbon\Carbon::parse($request->contract_start_date);
            $endDate = $startDate->copy()->addMonths($request->contract_period);
            
            if ($endDate->isPast()) {
                return back()->withErrors(['contract_period'=>'Thời gian hợp đồng không hợp lệ. Ngày kết thúc không được trong quá khứ.'])->withInput();
            }
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
        
        // Xử lý cập nhật thông tin hợp đồng
        if ($user->activeContract) {
            $contractData = [];
            
            if ($request->filled('contract_salary')) {
                $contractData['probation_salary'] = $request->contract_salary;
            }
            if ($request->filled('contract_period')) {
                $contractData['probation_period'] = $request->contract_period;
                // Cập nhật ngày kết thúc dựa trên ngày bắt đầu và thời gian hợp đồng
                if ($request->filled('contract_start_date')) {
                    $startDate = $request->contract_start_date;
                } else {
                    $startDate = $user->activeContract->start_date->format('Y-m-d');
                }
                $contractData['end_date'] = \Carbon\Carbon::parse($startDate)->addMonths($request->contract_period);
            }
            if ($request->filled('contract_start_date')) {
                $contractData['start_date'] = $request->contract_start_date;
                // Cập nhật ngày kết thúc nếu có thời gian hợp đồng
                if ($request->filled('contract_period')) {
                    $contractData['end_date'] = \Carbon\Carbon::parse($request->contract_start_date)->addMonths($request->contract_period);
                }
            }
            if ($request->filled('contract_status')) {
                $contractData['status'] = $request->contract_status;
            }
            
            // Cập nhật hợp đồng nếu có dữ liệu thay đổi
            if (!empty($contractData)) {
                $user->activeContract->update($contractData);
            }
        }

        // Xử lý upload hình ảnh hợp đồng (chỉ cho nhân viên chính thức)
        if ($request->hasFile('contract_images')) {
            $activeContract = $user->contracts()->where('status', 'active')->first();
            
            if ($activeContract) {
                foreach ($request->file('contract_images') as $index => $image) {
                    $fileName = time() . '_contract_' . $user->id . '_' . ($index + 1) . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/contracts', $fileName);
                    
                    $activeContract->images()->create([
                        'image_path' => asset('storage/contracts/' . $fileName),
                        'page_number' => $activeContract->images()->count() + $index + 1,
                    ]);
                }
            }
        }
        
        if (!empty($data['password'])) $data['password'] = bcrypt($data['password']); else unset($data['password']);
        
        $user->update($data);
        
        $message = 'Cập nhật thông tin thành công.';
        if ($user->activeContract && ($request->filled('contract_salary') || $request->filled('contract_period') || $request->filled('contract_start_date') || $request->filled('contract_status'))) {
            $message = 'Cập nhật thông tin và hợp đồng thành công.';
        }
        
        return redirect()->route('users.index')->with('success', $message);
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
