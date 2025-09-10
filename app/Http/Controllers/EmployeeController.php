<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeeContract;
use App\Models\EmployeeSalary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    /**
     * Hiển thị danh sách nhân viên mới
     */
    public function newEmployeesIndex(Request $request)
    {
        $query = User::where('employee_type', 'new')
                    ->with(['department', 'departments', 'contracts' => function($query) {
                        $query->where('status', 'active')->latest();
                    }]);

        // Tìm kiếm theo tên, email hoặc số điện thoại
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        // Lọc theo phòng ban
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            if ($request->status === 'no_contract') {
                $query->whereDoesntHave('contracts');
            } else {
                $query->whereHas('contracts', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }
        }

        $newEmployees = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('employees.new.index', compact('newEmployees'));
    }

    /**
     * Hiển thị form tạo nhân viên mới
     */
    public function newEmployeesCreate()
    {
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('employees.new.create', compact('departments'));
    }

    /**
     * Lưu nhân viên mới
     */
    public function newEmployeesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'department_ids' => 'required|array',
            'department_ids.*' => 'exists:departments,id',
            'probation_salary' => 'required|numeric|min:0',
            'probation_period' => 'required|integer|min:1|max:12', // tháng
        ]);

        try {
            // Tạo user mới
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => 'employee', // Mặc định là employee
                'department_id' => $request->department_ids[0], // Phòng ban đầu tiên
                'employee_type' => 'new',
                'password' => bcrypt('password123'), // mật khẩu mặc định
            ]);

            // Xử lý multiple departments
            if (!empty($request->department_ids)) {
                $departmentsToAttach = [];
                foreach ($request->department_ids as $deptId) {
                    $departmentsToAttach[$deptId] = [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $user->departments()->attach($departmentsToAttach);
            }

            // Lưu thông tin hợp đồng
            $contract = EmployeeContract::create([
                'user_id' => $user->id,
                'probation_salary' => $request->probation_salary,
                'probation_period' => $request->probation_period,
                'start_date' => now(),
                'status' => 'active',
            ]);



            return redirect()->route('employees.new.index')->with('success', 'Đã tạo nhân viên mới thành công!');

        } catch (\Exception $e) {
            Log::error('Error creating new employee: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo nhân viên mới.'])->withInput();
        }
    }

    /**
     * Chuyển nhân viên mới thành nhân viên chính thức
     */
    public function convertToOfficial(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,manager,employee',
            'official_salary' => 'required|numeric|min:0',
            'official_start_date' => 'required|date',
            'contract_duration' => 'required|integer|min:1|max:60', // tháng
        ]);

        // Kiểm tra quyền
        $currentUser = auth()->user();
        
        // Manager không thể tạo admin
        if ($currentUser->isManager() && $request->role === 'admin') {
            abort(403, 'Bạn không có quyền tạo tài khoản admin.');
        }

        try {
            // Log thông tin trước khi chuyển đổi
            \Log::info('Converting employee to official', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'old_employee_type' => $user->employee_type,
                'new_role' => $request->role,
                'official_salary' => $request->official_salary,
                'contract_duration' => $request->contract_duration,
            ]);

            // Bắt đầu transaction
            \DB::beginTransaction();

            $user->update([
                'employee_type' => 'official',
                'role' => $request->role,
            ]);
            \Log::info('User updated successfully', ['user_id' => $user->id, 'new_employee_type' => $user->employee_type]);

            // Tạo lương chính thức
            $salary = EmployeeSalary::create([
                'user_id' => $user->id,
                'gross_salary' => $request->official_salary,
                'basic_salary' => $request->official_salary,
                'net_salary' => $request->official_salary,
                'effective_date' => $request->official_start_date,
                'status' => 'active',
            ]);
            \Log::info('Salary created successfully', ['salary_id' => $salary->id, 'amount' => $salary->gross_salary]);

            // Cập nhật trạng thái hợp đồng thử việc cũ thành completed
            $oldContracts = $user->contracts()->where('status', 'active')->get();
            $user->contracts()->where('status', 'active')->update(['status' => 'completed']);
            \Log::info('Old contracts marked as completed', ['count' => $oldContracts->count()]);

            // Tạo hợp đồng chính thức mới
            $newContract = EmployeeContract::create([
                'user_id' => $user->id,
                'probation_salary' => $request->official_salary,
                'probation_period' => $request->contract_duration,
                'start_date' => $request->official_start_date,
                'end_date' => \Carbon\Carbon::parse($request->official_start_date)->addMonths($request->contract_duration),
                'status' => 'active',
            ]);
            \Log::info('New contract created successfully', [
                'contract_id' => $newContract->id,
                'salary' => $newContract->probation_salary,
                'period' => $newContract->probation_period,
                'status' => $newContract->status
            ]);

            // Đảm bảo chỉ có một hợp đồng active
            $latestContract = $user->contracts()->where('status', 'active')->latest()->first();
            if ($latestContract) {
                $user->contracts()->where('status', 'active')->where('id', '!=', $latestContract->id)->update(['status' => 'completed']);
                \Log::info('Ensured only one active contract', ['active_contract_id' => $latestContract->id]);
            }

            // Commit transaction
            \DB::commit();
            \Log::info('Transaction committed successfully');

            return redirect()->route('users.index')->with('success', 'Đã chuyển nhân viên thành chính thức!');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error converting employee: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi chuyển đổi nhân viên: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị trang thông báo cho nhân viên mới
     */
    public function newEmployeeNotice()
    {
        $user = auth()->user();
        
        // Admin và Director luôn luôn active, chuyển về dashboard
        if ($user->isAdmin() || $user->isDirector() || $user->account_status === 'active') {
            return redirect()->route('dashboard');
        }
        
        return view('employees.new.notice', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa nhân viên mới
     */
    public function newEmployeesEdit(User $user)
    {
        // Kiểm tra quyền
        if (!auth()->user()->isAdmin() && !auth()->user()->isDirector()) {
            abort(403, 'Bạn không có quyền chỉnh sửa nhân viên.');
        }
        
        // Kiểm tra xem user có phải là nhân viên mới không
        if ($user->employee_type !== 'new') {
            abort(404, 'Chỉ có thể chỉnh sửa nhân viên mới.');
        }
        
        $user->load(['departments']);
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('admin.users.edit-new', compact('user', 'departments'));
    }

    /**
     * Cập nhật thông tin nhân viên mới
     */
    public function newEmployeesUpdate(Request $request, User $user)
    {
        // Kiểm tra quyền
        if (!auth()->user()->isAdmin() && !auth()->user()->isDirector()) {
            abort(403, 'Bạn không có quyền cập nhật nhân viên.');
        }
        
        // Kiểm tra xem user có phải là nhân viên mới không
        if ($user->employee_type !== 'new') {
            abort(404, 'Chỉ có thể cập nhật nhân viên mới.');
        }
        
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email,' . $user->id,
            'phone'=>'nullable|string|max:20|unique:users,phone,' . $user->id,
            'password'=>'nullable|min:8|confirmed',
            'role'=>'required|in:manager,employee',
            'department_ids'=>'required|array',
            'department_ids.*'=>'exists:departments,id',
            'account_status'=>($user->isAdmin() || $user->isDirector()) ? 'nullable|in:active,inactive' : 'required|in:active,inactive',
            'add_contract'=>'nullable|boolean',
            // Thông tin hợp đồng thử việc
            'probation_salary'=>'nullable|numeric|min:0',
            'probation_period'=>'nullable|integer|min:1|max:12',
            'start_date'=>'nullable|date',
            'contract_status'=>'nullable|in:active,completed,terminated',
        ]);
        
        // Kiểm tra quyền
        $currentUser = auth()->user();
        
        // Manager không thể tạo admin
        if ($currentUser->isManager() && $data['role'] === 'admin') {
            abort(403, 'Bạn không có quyền tạo tài khoản admin.');
        }
        
        // Validate thông tin hợp đồng nếu có gửi lên
        if ($request->filled('probation_salary') || $request->filled('probation_period') || $request->filled('start_date')) {
            // Chỉ validate các trường được gửi lên
            $contractValidation = [];
            if ($request->filled('probation_salary')) {
                $contractValidation['probation_salary'] = 'required|numeric|min:0';
            }
            if ($request->filled('probation_period')) {
                $contractValidation['probation_period'] = 'required|integer|min:1|max:12';
            }
            if ($request->filled('start_date')) {
                $contractValidation['start_date'] = 'required|date';
            }
            
            if (!empty($contractValidation)) {
                $request->validate($contractValidation);
            }
        }
        
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        
        // Xử lý department_ids - lưu phòng ban đầu tiên làm department_id
        if (!empty($data['department_ids'])) {
            $data['department_id'] = $data['department_ids'][0];
        }
        
        // Admin và Director luôn luôn active
        if ($user->isAdmin() || $user->isDirector()) {
            $data['account_status'] = 'active';
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
        
        // Xử lý hợp đồng thử việc
        if ($user->activeContract) {
            // Cập nhật hợp đồng hiện tại
            $contract = $user->activeContract;
            $contractData = [];
            

            
            // Chỉ cập nhật các trường được gửi lên
            if ($request->filled('probation_salary')) {
                $contractData['probation_salary'] = $request->probation_salary;
            }
            if ($request->filled('probation_period')) {
                $contractData['probation_period'] = $request->probation_period;
            }
            if ($request->filled('start_date')) {
                $contractData['start_date'] = $request->start_date;
            }
            if ($request->filled('contract_status')) {
                $contractData['status'] = $request->contract_status;
            }
            
            // Cập nhật nếu có dữ liệu thay đổi
            if (!empty($contractData)) {
                $contract->update($contractData);
            }
        } elseif ($request->has('add_contract') && $request->filled('probation_salary') && $request->filled('probation_period') && $request->filled('start_date')) {
            // Tạo hợp đồng thử việc mới
            EmployeeContract::create([
                'user_id' => $user->id,
                'probation_salary' => $request->probation_salary,
                'probation_period' => $request->probation_period,
                'start_date' => $request->start_date,
                'status' => 'active',
            ]);
        }
        
        $message = 'Cập nhật thông tin thành công.';
        if ($user->activeContract) {
            $message = 'Cập nhật thông tin và hợp đồng thành công.';
        } elseif ($request->has('add_contract')) {
            $message = 'Cập nhật thông tin và tạo hợp đồng thử việc thành công.';
        }
        
        return redirect()->route('employees.new.index')->with('success', $message);
    }

    /**
     * Hiển thị quản lý lương
     */
    public function salaryIndex()
    {
        $salaries = EmployeeSalary::with(['user.department'])
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        return view('employees.salary.index', compact('salaries'));
    }

    /**
     * Hiển thị form chỉnh sửa lương
     */
    public function salaryEdit(EmployeeSalary $salary)
    {
        return view('employees.salary.edit', compact('salary'));
    }

    /**
     * Cập nhật lương
     */
    public function salaryUpdate(Request $request, EmployeeSalary $salary)
    {
        $request->validate([
            'gross_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        try {
            $netSalary = $request->gross_salary + $request->allowance + $request->bonus 
                        - $request->deduction - $request->insurance - $request->tax;

            $salary->update([
                'gross_salary' => $request->gross_salary,
                'allowance' => $request->allowance ?? 0,
                'bonus' => $request->bonus ?? 0,
                'deduction' => $request->deduction ?? 0,
                'insurance' => $request->insurance ?? 0,
                'tax' => $request->tax ?? 0,
                'net_salary' => $netSalary,
            ]);

            return redirect()->route('employees.salary.index')->with('success', 'Đã cập nhật lương thành công!');

        } catch (\Exception $e) {
            Log::error('Error updating salary: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật lương.']);
        }
    }

    /**
     * Xóa nhân viên mới
     */
    public function newEmployeesDestroy(User $employee)
    {
        // Kiểm tra quyền
        if (!auth()->user()->isAdmin() && !auth()->user()->isDirector()) {
            abort(403, 'Không đủ quyền thực hiện thao tác này.');
        }

        // Kiểm tra nhân viên có phải là nhân viên mới không
        if ($employee->employee_type !== 'new') {
            abort(404, 'Nhân viên không tồn tại hoặc không phải nhân viên mới.');
        }

        try {
            // Xóa các bản ghi liên quan
            $employee->contracts()->delete();
            $employee->salaries()->delete();
            
            // Xóa avatar nếu có
            if ($employee->avatar && Storage::exists('public/' . $employee->avatar)) {
                Storage::delete('public/' . $employee->avatar);
            }
            
            // Xóa nhân viên
            $employee->delete();

            return redirect()->route('employees.new.index')
                            ->with('success', 'Nhân viên đã được xóa thành công.');
        } catch (\Exception $e) {
            Log::error('Error deleting new employee: ' . $e->getMessage());
            return redirect()->route('employees.new.index')
                            ->with('error', 'Có lỗi xảy ra khi xóa nhân viên. Vui lòng thử lại.');
        }
    }
}
