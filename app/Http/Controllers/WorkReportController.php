<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WorkReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isEmployee()) {
            return $this->employeeIndex();
        } elseif ($user->isManager()) {
            return $this->managerIndex();
        } else {
            return $this->adminIndex();
        }
    }

    // Employee view - Tạo báo cáo
    private function employeeIndex()
    {
        $user = Auth::user();
        $years = WorkReport::getAvailableYears($user->id);
        $currentYear = now()->year;
        
        // Thêm năm hiện tại nếu chưa có
        if (!$years->contains($currentYear)) {
            $years->push($currentYear);
        }
        
        return view('work-reports.employee.index', compact('years', 'currentYear'));
    }

    // Manager view - Tạo báo cáo + Quản lý báo cáo
    private function managerIndex()
    {
        $user = Auth::user();
        $years = WorkReport::getAvailableYears();
        $currentYear = now()->year;
        
        // Thêm năm hiện tại nếu chưa có
        if (!$years->contains($currentYear)) {
            $years->push($currentYear);
        }
        
        // Lấy danh sách employees trong phòng ban
        $employees = User::where('department_id', $user->department_id)
                        ->where('role', 'employee')
                        ->orderBy('name')
                        ->get();
        
        return view('work-reports.manager.index', compact('years', 'currentYear', 'employees'));
    }

    // Admin view - Tương tự Manager nhưng xem được đa phòng ban
    private function adminIndex()
    {
        $years = WorkReport::getAvailableYears();
        $currentYear = now()->year;
        
        // Thêm năm hiện tại nếu chưa có
        if (!$years->contains($currentYear)) {
            $years->push($currentYear);
        }
        
        // Lấy tất cả departments
        $departments = Department::orderBy('name')->get();
        
        return view('work-reports.admin.index', compact('years', 'currentYear', 'departments'));
    }

    // Tạo báo cáo mới
    public function create(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'week' => 'required|integer|min:1|max:53',
        ]);

        $year = $request->year;
        $month = $request->month;
        $week = $request->week;

        // Kiểm tra xem đã có báo cáo cho tuần này chưa
        $existingReports = WorkReport::getWeekReports($year, $month, $week, $user->id);
        
        return view('work-reports.create', compact('year', 'month', 'week', 'existingReports'));
    }

    // Lưu báo cáo
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer',
            'week' => 'required|integer',
            'report_dates' => 'required|array',
            'report_dates.*' => 'required|date',
            'daily_works' => 'required|array',
            'daily_works.*' => 'required|string',
            'difficulties' => 'nullable|array',
            'difficulties.*' => 'nullable|string',
            'comments' => 'nullable|array',
            'comments.*' => 'nullable|string',
            'custom_fields' => 'nullable|array'
        ]);

        $createdCount = 0;
        $errors = [];

        // Xử lý từng hàng báo cáo
        foreach ($request->report_dates as $index => $reportDate) {
            // Kiểm tra xem đã có báo cáo cho ngày này chưa
            $existingReport = WorkReport::where('user_id', $user->id)
                                       ->where('report_date', $reportDate)
                                       ->first();

            if ($existingReport) {
                $errors[] = "Đã có báo cáo cho ngày {$reportDate}.";
                continue;
            }

            // Tạo báo cáo mới
            WorkReport::create([
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'year' => $request->year,
                'month' => $request->month,
                'week' => $request->week,
                'report_date' => $reportDate,
                'daily_work' => $request->daily_works[$index],
                'difficulties' => $request->difficulties[$index] ?? null,
                'comments' => $request->comments[$index] ?? null,
                'custom_fields' => $request->custom_fields
            ]);

            $createdCount++;
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $message = $createdCount > 1 
            ? "Đã tạo thành công {$createdCount} báo cáo." 
            : "Báo cáo đã được tạo thành công.";

        return redirect()->route('work-reports.index')
                        ->with('success', $message);
    }

    // Cập nhật báo cáo
    public function update(Request $request, WorkReport $workReport)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền
        if ($workReport->user_id !== $user->id && !$user->isManager() && !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền cập nhật báo cáo này.');
        }

        $request->validate([
            'daily_work' => 'required|string',
            'difficulties' => 'nullable|string',
            'comments' => 'nullable|string',
            'custom_fields' => 'nullable|array'
        ]);

        $workReport->update([
            'daily_work' => $request->daily_work,
            'difficulties' => $request->difficulties,
            'comments' => $request->comments,
            'custom_fields' => $request->custom_fields
        ]);

        return back()->with('success', 'Báo cáo đã được cập nhật thành công.');
    }

    // Xem báo cáo theo tuần
    public function showWeek(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer',
            'week' => 'required|integer',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $year = $request->year;
        $month = $request->month;
        $week = $request->week;
        $targetUserId = $request->user_id;

        // Kiểm tra quyền
        if ($targetUserId) {
            $targetUser = User::find($targetUserId);
            
            if ($user->isEmployee() && $targetUserId !== $user->id) {
                abort(403, 'Bạn chỉ có thể xem báo cáo của mình.');
            }
            
            if ($user->isManager() && $targetUser->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xem báo cáo của nhân viên cùng phòng ban.');
            }
        } else {
            $targetUserId = $user->id;
        }

        $reports = WorkReport::getWeekReports($year, $month, $week, $targetUserId);
        
        return response()->json([
            'reports' => $reports,
            'week_info' => [
                'year' => $year,
                'month' => $month,
                'week' => $week
            ]
        ]);
    }

    // API để lấy danh sách tháng cho năm
    public function getMonths(Request $request)
    {
        $request->validate(['year' => 'required|integer']);
        
        $user = Auth::user();
        $userId = $user->isEmployee() ? $user->id : null;
        
        $months = WorkReport::getAvailableMonths($request->year, $userId);
        
        return response()->json($months);
    }

    // API để lấy danh sách tuần cho tháng
    public function getWeeks(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer'
        ]);
        
        $user = Auth::user();
        $userId = $user->isEmployee() ? $user->id : null;
        
        $weeks = WorkReport::getAvailableWeeks($request->year, $request->month, $userId);
        
        return response()->json($weeks);
    }

    // API để lấy danh sách employees theo department (cho Manager/Admin)
    public function getEmployeesByDepartment(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $request->validate(['department_id' => 'required|exists:departments,id']);
        
        // Manager chỉ có thể xem employees cùng phòng ban
        if ($user->isManager() && $request->department_id != $user->department_id) {
            abort(403, 'Bạn chỉ có thể xem nhân viên cùng phòng ban.');
        }

        $employees = User::where('department_id', $request->department_id)
                        ->where('role', 'employee')
                        ->orderBy('name')
                        ->get(['id', 'name']);

        return response()->json($employees);
    }

    // Xóa báo cáo
    public function destroy(WorkReport $workReport)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền
        if ($workReport->user_id !== $user->id && !$user->isManager() && !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền xóa báo cáo này.');
        }

        // Manager chỉ có thể xóa báo cáo của nhân viên cùng phòng ban
        if ($user->isManager() && $workReport->department_id !== $user->department_id) {
            abort(403, 'Bạn chỉ có thể xóa báo cáo của nhân viên cùng phòng ban.');
        }

        $workReport->delete();

        return back()->with('success', 'Báo cáo đã được xóa thành công.');
    }
}
