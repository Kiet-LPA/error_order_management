<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền xem danh sách người dùng.');
        }
        
        // Base query - thêm with('departments') để tránh N+1 query
        $query = User::with(['department', 'departments'])->where('employee_type', 'official');
        
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
            'director' => User::where('employee_type', 'official')->where('role', 'director')->count(),
            'manager' => User::where('employee_type', 'official')->where('role', 'manager')->count(),
            'employee' => User::where('employee_type', 'official')->where('role', 'employee')->count(),
        ];
        
        // Manager chỉ thấy stats của phòng ban mình
        if ($user->isManager()) {
            $stats = [
                'total' => User::where('employee_type', 'official')->where('department_id', $user->department_id)->count(),
                'admin' => User::where('employee_type', 'official')->where('department_id', $user->department_id)->where('role', 'admin')->count(),
                'director' => User::where('employee_type', 'official')->where('department_id', $user->department_id)->where('role', 'director')->count(),
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
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền tạo người dùng.');
        }
        
        if ($user->isAdmin()) {
            // Admin có thể tạo user cho bất kỳ phòng ban nào
            $departments = Department::orderBy('name')->get();
        } elseif ($user->isDirector()) {
            // Director có thể tạo user cho tất cả phòng ban (như Admin)
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
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền tạo người dùng.');
        }
        
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>['nullable','email','unique:users,email'],
            'phone'=>['nullable','string','max:20','unique:users,phone'],
            'password'=>'required|min:8|confirmed',
            'role'=>'required|in:admin,director,manager,employee',
            'department_ids'=>'nullable|array',
            'department_ids.*'=>'exists:departments,id',
            'position'=>'nullable|string|max:255',
            'social_insurance_number'=>'nullable|string|max:50',
            'health_insurance_number'=>'nullable|string|max:50',
            'personal_identification_number'=>'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'can_manage_cars' => 'nullable|boolean',
        ]);
        
        // Kiểm tra ít nhất phải có email hoặc số điện thoại
        if (empty($data['email']) && empty($data['phone'])) {
            return back()->withErrors(['email'=>'Phải có ít nhất email hoặc số điện thoại.'])->withInput();
        }
        
        // Bắt buộc department cho manager/employee
        if (in_array($data['role'], ['manager','employee']) && empty($data['department_ids'])) {
            return back()->withErrors(['department_ids'=>'Bắt buộc chọn ít nhất một phòng ban.'])->withInput();
        }
        
        // Director và Admin: nếu không chọn phòng ban nào thì mặc định quản lý tất cả phòng ban
        if (in_array($data['role'], ['director', 'admin']) && empty($data['department_ids'])) {
            $data['department_ids'] = Department::pluck('id')->toArray();
        }
        
        // Manager chỉ có thể tạo user cho phòng ban của mình
        if ($user->isManager() && !empty($data['department_ids'])) {
            $userDepartments = $user->departments->pluck('id')->toArray();
            $hasValidDepartment = false;
            foreach ($data['department_ids'] as $deptId) {
                if (in_array($deptId, $userDepartments)) {
                    $hasValidDepartment = true;
                    break;
                }
            }
            if (!$hasValidDepartment) {
                abort(403, 'Bạn chỉ có thể tạo người dùng cho phòng ban của mình.');
            }
        }
        
        // Director có thể tạo user cho tất cả phòng ban (như Admin)
        // Không cần kiểm tra gì thêm
        
        // Manager không thể tạo admin
        if ($user->isManager() && $data['role'] === 'admin') {
            abort(403, 'Bạn không có quyền tạo tài khoản admin.');
        }
        
        // Director không thể tạo admin
        if ($user->isDirector() && $data['role'] === 'admin') {
            abort(403, 'Bạn không có quyền tạo tài khoản admin.');
        }
        
        $data['password'] = bcrypt($data['password']);
        
        // Xử lý department_ids - lưu phòng ban đầu tiên làm department_id
        if (!empty($data['department_ids'])) {
            $data['department_id'] = $data['department_ids'][0]; // Phòng ban đầu tiên
        }
        
        // Xử lý upload avatar
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_new.' . $file->getClientOriginalExtension();
            $file->storeAs('avatars', $filename, 'public');
            $data['avatar'] = $filename; // Chỉ lưu filename
            \Log::info('Created avatar for new user: ' . $filename);
        }
        
        $newUser = User::create($data);
        
        // Xử lý multiple departments
        if (!empty($data['department_ids'])) {
            $departmentsToAttach = [];
            foreach ($data['department_ids'] as $deptId) {
                $departmentsToAttach[$deptId] = [
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $newUser->departments()->attach($departmentsToAttach);
        }
        
        return redirect()->route('users.index')->with('success','Tạo người dùng thành công.');
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        // Kiểm tra quyền chỉnh sửa user
        if (!$currentUser->isAdmin() && !$currentUser->isDirector() && !$currentUser->isManager()) {
            abort(403, 'Bạn không có quyền chỉnh sửa người dùng.');
        }
        
        // Manager chỉ có thể chỉnh sửa user cùng phòng ban
        if ($currentUser->isManager() && $user->department_id !== $currentUser->department_id) {
            abort(403, 'Bạn chỉ có thể chỉnh sửa người dùng cùng phòng ban.');
        }
        
        // Director không thể chỉnh sửa Admin hoặc Director khác - sẽ disable các trường thay vì abort
        $canEdit = !($currentUser->isDirector() && ($user->isAdmin() || $user->isDirector()));
        
        // Chỉ xử lý nhân viên chính thức
        if ($user->employee_type === 'new') {
            abort(404, 'Vui lòng sử dụng form chỉnh sửa nhân viên mới.');
        }
        
        // Load các relationship cần thiết
        $user->load(['department', 'departments', 'contracts.images', 'activeContract']);
        
        $departments = Department::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'departments', 'canEdit'));
    }

    public function show(User $user)
    {
        $currentUser = auth()->user();
        
        // Kiểm tra quyền xem user
        if (!$currentUser->isAdmin() && !$currentUser->isDirector() && !$currentUser->isManager()) {
            abort(403, 'Bạn không có quyền xem thông tin người dùng.');
        }
        
        // Manager chỉ có thể xem user cùng phòng ban
        if ($currentUser->isManager() && $user->department_id !== $currentUser->department_id) {
            abort(403, 'Bạn chỉ có thể xem thông tin người dùng cùng phòng ban.');
        }
        
        // Director không thể xem Admin
        if ($currentUser->isDirector() && $user->isAdmin()) {
            abort(403, 'Director không thể xem thông tin Admin.');
        }
        
        // Load các relationship cần thiết
        $user->load(['department', 'departments', 'contracts.images', 'salary']);
        
        return view('admin.users.show', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Kiểm tra quyền cập nhật user
        if (!$currentUser->isAdmin() && !$currentUser->isDirector() && !$currentUser->isManager()) {
            abort(403, 'Bạn không có quyền cập nhật người dùng.');
        }
        
        // Manager chỉ có thể cập nhật user cùng phòng ban
        if ($currentUser->isManager() && $user->department_id !== $currentUser->department_id) {
            abort(403, 'Bạn chỉ có thể cập nhật người dùng cùng phòng ban.');
        }
        
        // Director không thể cập nhật Admin hoặc Director khác - return về trang edit với thông báo
        if ($currentUser->isDirector() && ($user->isAdmin() || $user->isDirector())) {
            return redirect()->route('users.edit', $user)->with('error', 'Director không thể chỉnh sửa Admin hoặc Director khác.');
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
            'role'=>'required|in:admin,director,manager,employee',
            'department_ids'=>'nullable|array',
            'department_ids.*'=>'exists:departments,id',
            'position'=>'nullable|string|max:255',
            'social_insurance_number'=>'nullable|string|max:50',
            'health_insurance_number'=>'nullable|string|max:50',
            'personal_identification_number'=>'nullable|string|max:50',
            'account_status'=>($user->isAdmin() || $user->isDirector()) ? 'nullable|in:active,inactive' : 'required|in:active,inactive',
            'contract_images.*'=>'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'can_manage_cars' => 'nullable|boolean',
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
        
        // Debug department_ids
        \Log::info('Department IDs received:', ['department_ids' => $data['department_ids'] ?? 'null']);
        \Log::info('User role:', ['role' => $data['role']]);
        
        if (in_array($data['role'], ['manager','employee']) && empty($data['department_ids'])) {
            \Log::warning('Validation failed: No departments selected for manager/employee');
            return back()->withErrors(['department_ids'=>'Bắt buộc chọn ít nhất một phòng ban.'])->withInput();
        }
        
        // Director và Admin: nếu không chọn phòng ban nào thì mặc định quản lý tất cả phòng ban
        if (in_array($data['role'], ['director', 'admin']) && empty($data['department_ids'])) {
            $data['department_ids'] = Department::pluck('id')->toArray();
        }
        
        // Manager chỉ có thể cập nhật user cho phòng ban của mình
        if ($currentUser->isManager() && !empty($data['department_ids'])) {
            $userDepartments = $currentUser->departments->pluck('id')->toArray();
            $hasValidDepartment = false;
            foreach ($data['department_ids'] as $deptId) {
                if (in_array($deptId, $userDepartments)) {
                    $hasValidDepartment = true;
                    break;
                }
            }
            if (!$hasValidDepartment) {
                abort(403, 'Bạn chỉ có thể cập nhật người dùng cho phòng ban của mình.');
            }
        }
        
        // Manager không thể tạo admin
        if ($currentUser->isManager() && $data['role'] === 'admin') {
            abort(403, 'Bạn không có quyền tạo tài khoản admin.');
        }
        
        // Director không thể tạo admin
        if ($currentUser->isDirector() && $data['role'] === 'admin') {
            abort(403, 'Director không có quyền tạo tài khoản admin.');
        }
        
        // Xử lý chuyển trạng thái nhân viên
        $oldPosition = $user->position;
        $newPosition = $data['position'];
        
        // Nếu chuyển từ "Nhân Viên Chính Thức" sang "Nhân Viên Thử Việc"
        if ($oldPosition == 'Nhân Viên Chính Thức' && $newPosition == 'Nhân Viên Thử Việc') {
            $data['employee_type'] = 'new';
        }
        // Nếu chuyển từ "Nhân Viên Thử Việc" sang "Nhân Viên Chính Thức"
        elseif ($oldPosition == 'Nhân Viên Thử Việc' && $newPosition == 'Nhân Viên Chính Thức') {
            $data['employee_type'] = 'official';
        }
        
        // Xử lý cập nhật thông tin hợp đồng
        $contractData = [];
        
        if ($request->filled('contract_salary')) {
            $contractData['probation_salary'] = $request->contract_salary;
        }
        if ($request->filled('contract_period')) {
            $contractData['probation_period'] = $request->contract_period;
        }
        if ($request->filled('contract_start_date')) {
            $contractData['start_date'] = $request->contract_start_date;
        }
        if ($request->filled('contract_status')) {
            $contractData['status'] = $request->contract_status;
        }
        
        // Tính toán ngày kết thúc nếu có đủ thông tin
        if ($request->filled('contract_start_date') && $request->filled('contract_period')) {
            $contractData['end_date'] = \Carbon\Carbon::parse($request->contract_start_date)->addMonths($request->contract_period);
        }
        
        // Nếu có hợp đồng hiện tại, cập nhật
        if ($user->activeContract && !empty($contractData)) {
            $user->activeContract->update($contractData);
        }
        // Nếu chưa có hợp đồng và có thông tin hợp đồng, tạo mới
        elseif (!$user->activeContract && !empty($contractData)) {
            // Đảm bảo có đủ thông tin cần thiết
            if (!isset($contractData['status'])) {
                $contractData['status'] = 'active';
            }
            if (!isset($contractData['start_date'])) {
                $contractData['start_date'] = now();
            }
            
            // Chỉ tạo hợp đồng nếu có đủ thông tin bắt buộc
            if (isset($contractData['probation_salary']) && isset($contractData['probation_period'])) {
                $user->contracts()->create($contractData);
            }
        }

        // Xử lý upload hình ảnh hợp đồng (chỉ cho nhân viên có phòng ban)
        if ($request->hasFile('contract_images') && $user->department_id) {
            $activeContract = $user->contracts()->where('status', 'active')->first();
            
            // Nếu chưa có hợp đồng active, tạo mới
            if (!$activeContract) {
                $activeContract = $user->contracts()->create([
                    'status' => 'active',
                    'start_date' => now(),
                    'probation_salary' => 0, // Giá trị mặc định
                    'probation_period' => 12, // Giá trị mặc định
                ]);
            }
            
            foreach ($request->file('contract_images') as $index => $image) {
                $fileName = time() . '_contract_' . $user->id . '_' . ($index + 1) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('contracts', $fileName, 'public');
                
                $activeContract->images()->create([
                    'image_path' => 'contracts/' . $fileName,
                    'page_number' => $activeContract->images()->count() + $index + 1,
                ]);
            }
        }
        
        if (!empty($data['password'])) $data['password'] = bcrypt($data['password']); else unset($data['password']);
        
        // Xử lý department_ids - lưu phòng ban đầu tiên làm department_id
        if (!empty($data['department_ids'])) {
            $data['department_id'] = $data['department_ids'][0];
        }
        
        // Admin và Director luôn luôn active
        if ($user->isAdmin() || $user->isDirector()) {
            $data['account_status'] = 'active';
        }
        
        // Xử lý upload avatar
        if ($request->hasFile('avatar')) {
            \Log::info('Admin editing user avatar for: ' . $user->id);
            
            // Xóa avatar cũ nếu có
            if ($user->avatar) {
                // Handle both formats
                if (strpos($user->avatar, 'avatars/') === 0) {
                    \Storage::disk('public')->delete($user->avatar);
                } else {
                    \Storage::disk('public')->delete('avatars/' . $user->avatar);
                }
                \Log::info('Deleted old avatar: ' . $user->avatar);
            }
            
            // Store new avatar với filename unique
            $file = $request->file('avatar');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('avatars', $filename, 'public');
            
            // Chỉ lưu filename, không lưu full path
            $data['avatar'] = $filename;
            \Log::info('Saved new avatar: ' . $filename);
        }
        
        $user->update($data);
        
        // Xử lý multiple departments
        if (isset($data['department_ids'])) {
            // Xóa tất cả departments cũ
            $user->departments()->detach();
            
            // Thêm departments mới
            if (!empty($data['department_ids'])) {
                $departmentsToAttach = [];
                foreach ($data['department_ids'] as $deptId) {
                    $departmentsToAttach[$deptId] = [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $user->departments()->attach($departmentsToAttach);
            }
        }
        
        $message = 'Cập nhật thông tin thành công.';
        
        // Kiểm tra xem có tạo hợp đồng mới không
        $hasNewContract = false;
        if (!$user->activeContract && (!empty($contractData) || $request->hasFile('contract_images'))) {
            $hasNewContract = true;
        }
        
        if (!empty($contractData) || $request->hasFile('contract_images')) {
            if ($hasNewContract) {
                $message = 'Cập nhật thông tin và tạo hợp đồng mới thành công.';
            } else {
                $message = 'Cập nhật thông tin và hợp đồng thành công.';
            }
        }
        
        // Thông báo chuyển trạng thái
        if ($oldPosition != $newPosition) {
            $message .= ' Đã chuyển trạng thái từ "' . $oldPosition . '" sang "' . $newPosition . '".';
        }
        
        
        return redirect()->route('users.index')->with('success', $message);
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        Log::info('=== USER DELETE ATTEMPT START ===', [
            'current_user_id' => $currentUser->id,
            'current_user_role' => $currentUser->role,
            'current_user_department_id' => $currentUser->department_id,
            'target_user_id' => $user->id,
            'target_user_role' => $user->role,
            'target_user_department_id' => $user->department_id,
        ]);
        
        // Kiểm tra quyền xóa user
        if (!$currentUser->isAdmin() && !$currentUser->isDirector() ) {
            Log::warning('User delete failed: Insufficient permissions', [
                'current_user_role' => $currentUser->role
            ]);
            abort(403, 'Bạn không có quyền xóa người dùng.');
        }
        

        
        // Director không thể xóa Admin
        if ($currentUser->isDirector() && $user->isAdmin()) {
            Log::warning('User delete failed: Director trying to delete Admin');
            abort(403, 'Director không thể xóa Admin.');
        }
        
        // Không thể xóa chính mình
        if ($currentUser->id === $user->id) {
            Log::warning('User delete failed: User trying to delete themselves');
            abort(403, 'Bạn không thể xóa tài khoản của chính mình.');
        }
        
        Log::info('Permission checks passed, starting deletion process');
        
        try {
            DB::beginTransaction();
            
            // Xóa các bản ghi liên quan trước (theo thứ tự để tránh foreign key constraint)
            
            // 1. Xóa work reports
            DB::table('work_reports')->where('user_id', $user->id)->delete();
            
            // 2. Xóa comments
            DB::table('comments')->where('user_id', $user->id)->delete();
            
            // 3. Xóa tasks (creator)
            DB::table('tasks')->where('creator_id', $user->id)->delete();
            
            // 4. Xóa task assignees
            DB::table('task_assignees')->where('user_id', $user->id)->delete();
            
            // 5. Xóa task followers
            DB::table('task_followers')->where('user_id', $user->id)->delete();
            
            // 6. Xóa task forwards
            //DB::table('task_forwards')->where('user_id', $user->id)->delete();
            // Xóa tất cả forward có liên quan đến user (người chuyển hoặc người nhận)
            DB::table('task_forwards')
            ->where('forwarded_by', $user->id)
            ->orWhere('forwarded_to', $user->id)
            ->delete();

            // 7. Xóa support requests
            //DB::table('support_requests')->where('user_id', $user->id)->delete();
            // Xóa tất cả support request có liên quan đến user (người gửi, người duyệt hoặc người chuyển tiếp)
            DB::table('support_requests')
            ->where('requester_id', $user->id)
            ->orWhere('approver_id', $user->id)
            ->orWhere('forwarded_by', $user->id)
            ->delete();

            // 8. Xóa forward requests
            //DB::table('forward_requests')->where('user_id', $user->id)->delete();
            // Xóa tất cả forward request có liên quan đến user (người gửi hoặc người nhận)
            DB::table('forward_requests')
            ->where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->delete();

            // 9. Xóa approval requests
            //DB::table('approval_requests')->where('user_id', $user->id)->delete();
            // Xóa tất cả approval requests có liên quan đến user
            DB::table('approval_requests')
            ->where('created_by_id', $user->id)
            ->orWhere('current_approver_id', $user->id)
            ->orWhere('approved_by_id', $user->id)
            ->orWhere('rejected_by_id', $user->id)
            ->delete();

            // 10. Xóa approval actions
            DB::table('approval_actions')->where('user_id', $user->id)->delete();
            
            // 11. Xóa notifications
            DB::table('notifications')->where('user_id', $user->id)->delete();
            
            // 12. Xóa gps requests
            DB::table('gps_requests')->where('user_id', $user->id)->delete();
            
            // 13. Xóa checkins
            DB::table('checkins')->where('user_id', $user->id)->delete();
            
            // 14. Xóa rentals
            DB::table('rentals')->where('user_id', $user->id)->delete();
            
            // 15. Xóa task submissions (bảng mới)
            DB::table('task_submissions')->where('user_id', $user->id)->delete();
            
            // 16. Xóa approval request approvers
            DB::table('approval_request_approvers')->where('user_id', $user->id)->delete();
            
            // 17. Xóa approval request followers
            DB::table('approval_request_followers')->where('user_id', $user->id)->delete();
            
            // 18. Xóa task activities
            DB::table('task_activities')->where('user_id', $user->id)->delete();
            
            // 19. Xóa support request followers
            DB::table('support_request_followers')->where('user_id', $user->id)->delete();
            
            // 20. Xóa support request comments
            DB::table('support_request_comments')->where('user_id', $user->id)->delete();
            
            // 21. Xóa support request activities
            DB::table('support_request_activities')->where('user_id', $user->id)->delete();
            
            // 22. Xóa sessions (đăng nhập)
            DB::table('sessions')->where('user_id', $user->id)->delete();
            
            // 23. Xóa task checklist items (assignee)
            DB::table('task_checklist_items')->where('assignee_id', $user->id)->delete();
            
            // 24. Xóa task subtasks (assignee)
            DB::table('task_subtasks')->where('assignee_id', $user->id)->delete();
            
            // 25. Xóa tasks (assignee - set null)
            DB::table('tasks')->where('assignee_id', $user->id)->update(['assignee_id' => null]);
            
            // 26. Xóa user departments
            $user->departments()->detach();
            
            // 27. Xóa contracts và salaries
            $user->contracts()->delete();
            $user->salaries()->delete();
            
            // 28. Xóa avatar nếu có
            if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
                Storage::delete('public/' . $user->avatar);
            }
            
            // 29. Cuối cùng mới xóa user
            $user->delete();
            
            DB::commit();
            Log::info('=== USER DELETE SUCCESS ===', [
                'deleted_user_id' => $user->id,
                'deleted_user_name' => $user->name
            ]);
            return back()->with('success','Đã xóa thành công.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== USER DELETE FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'target_user_id' => $user->id
            ]);
            return back()->with('error', 'Có lỗi xảy ra khi xóa người dùng: ' . $e->getMessage());
        }
    }

    public function deleteContractImage($imageId)
    {
        try {
            $image = \App\Models\ContractImage::findOrFail($imageId);
            
            // Check if user has permission to delete this image
            $user = auth()->user();
            if (!$user->isAdmin() && !$user->isDirector()) {
                return response()->json(['success' => false, 'message' => 'Không có quyền xóa ảnh'], 403);
            }
            
            // Delete file from storage
            if (Storage::exists('public/' . $image->image_path)) {
                Storage::delete('public/' . $image->image_path);
            }
            
            // Delete from database
            $image->delete();
            
            return response()->json(['success' => true, 'message' => 'Đã xóa ảnh thành công']);
            
        } catch (\Exception $e) {
            Log::error('Error deleting contract image: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa ảnh'], 500);
        }
    }

    public function viewContractImage($imageId)
    {
        try {
            $image = \App\Models\ContractImage::findOrFail($imageId);
            
            // Check if user has permission to view this image
            $user = auth()->user();
            if (!$user->isAdmin() && !$user->isDirector()) {
                abort(403, 'Không có quyền xem ảnh');
            }
            
            // Check if file exists
            $filePath = storage_path('app/public/' . $image->image_path);
            if (!file_exists($filePath)) {
                abort(404, 'File không tồn tại');
            }
            
            return response()->file($filePath);
            
        } catch (\Exception $e) {
            Log::error('Error viewing contract image: ' . $e->getMessage());
            abort(404, 'Không thể hiển thị ảnh');
        }
    }

    public function debugContractImages(User $user)
    {
        $debug = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'has_active_contract' => $user->activeContract ? true : false,
        ];

        if ($user->activeContract) {
            $contract = $user->activeContract;
            $debug['contract'] = [
                'id' => $contract->id,
                'status' => $contract->status,
                'images_count' => $contract->images->count(),
                'images' => []
            ];

            foreach ($contract->images as $image) {
                $filePath = storage_path('app/public/' . $image->image_path);
                $debug['contract']['images'][] = [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                    'full_path' => $filePath,
                    'file_exists' => file_exists($filePath),
                    'page_number' => $image->page_number,
                ];
            }
        }

        return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
    }
}
