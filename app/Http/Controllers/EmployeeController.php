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
    public function newEmployeesIndex()
    {
        $newEmployees = User::where('employee_type', 'new')
                           ->with(['department', 'contracts'])
                           ->orderBy('created_at', 'desc')
                           ->get();

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
            'department_id' => 'required|exists:departments,id',
            'probation_salary' => 'required|numeric|min:0',
            'probation_period' => 'required|integer|min:1|max:12', // tháng
        ]);

        try {
            // Tạo user mới
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'employee_type' => 'new',
                'password' => bcrypt('password123'), // mật khẩu mặc định
            ]);

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
            'official_salary' => 'required|numeric|min:0',
            'official_start_date' => 'required|date',
            'contract_duration' => 'required|integer|min:1|max:60', // tháng
        ]);

        try {
            $user->update([
                'employee_type' => 'official',
            ]);

            // Tạo lương chính thức
            EmployeeSalary::create([
                'user_id' => $user->id,
                'gross_salary' => $request->official_salary,
                'net_salary' => $request->official_salary,
                'effective_date' => $request->official_start_date,
                'status' => 'active',
            ]);

            // Tạo hợp đồng chính thức
            EmployeeContract::create([
                'user_id' => $user->id,
                'probation_salary' => $request->official_salary,
                'probation_period' => $request->contract_duration,
                'start_date' => $request->official_start_date,
                'end_date' => \Carbon\Carbon::parse($request->official_start_date)->addMonths($request->contract_duration),
                'status' => 'active',
            ]);

            // Cập nhật trạng thái hợp đồng thử việc
            $user->contracts()->where('status', 'active')->update(['status' => 'completed']);

            return redirect()->route('users.index')->with('success', 'Đã chuyển nhân viên thành chính thức!');

        } catch (\Exception $e) {
            Log::error('Error converting employee: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi chuyển đổi nhân viên.']);
        }
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
}
