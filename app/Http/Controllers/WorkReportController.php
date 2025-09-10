<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use App\Models\User;
use App\Models\Department;
use App\Services\NotificationService;
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
        $currentYear = now()->year;
        
        return view('work-reports.employee.index', compact('currentYear'));
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
        
        // Lấy danh sách departments (chỉ phòng ban của manager)
        $departments = Department::where('id', $user->department_id)->get();
        
        return view('work-reports.manager.index', compact('years', 'currentYear', 'employees', 'departments'));
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

    // Chọn ngày báo cáo
    public function selectDate()
    {
        return view('work-reports.select-date');
    }

    // Tạo báo cáo mới
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra nếu có selected_date từ form select-date
        if ($request->has('selected_date')) {
            $selectedDate = Carbon::parse($request->selected_date);
            $year = $selectedDate->year;
            $week = $selectedDate->weekOfYear;
            $weekInfo = WorkReport::getWeekDates($year, $week);
            $selectedDateFormatted = $selectedDate->format('Y-m-d');
        } else {
            // Lấy thông tin tuần hiện tại để hiển thị
            $currentDate = now();
            $year = $currentDate->year;
            $week = $currentDate->weekOfYear;
            $weekInfo = WorkReport::getWeekDates($year, $week);
            $selectedDateFormatted = $currentDate->format('Y-m-d');
        }

        return view('work-reports.create', compact('year', 'week', 'weekInfo', 'selectedDateFormatted'));
    }

    // Hiển thị form chỉnh sửa báo cáo
    public function edit(WorkReport $workReport)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền
        if ($workReport->user_id !== $user->id && !$user->isManager() && !$user->isDirector() && !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền chỉnh sửa báo cáo này.');
        }

        // Manager chỉ có thể chỉnh sửa báo cáo của nhân viên cùng phòng ban
        if ($user->isManager() && $workReport->department_id !== $user->department_id) {
            abort(403, 'Bạn chỉ có thể chỉnh sửa báo cáo của nhân viên cùng phòng ban.');
        }

        return view('work-reports.edit', compact('workReport'));
    }



    // Lưu báo cáo
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra user có department_id không (trừ admin)
        if (!$user->department_id && !$user->isDirector() && !$user->isAdmin()) {
            return back()->withErrors(['department' => 'Bạn chưa được phân công vào phòng ban nào. Vui lòng liên hệ quản trị viên.'])->withInput();
        }
        
        $request->validate([
            'report_dates' => 'required|array',
            'report_dates.*' => 'required|date_format:Y-m-d',
            'daily_works' => 'required|array',
            'daily_works.*' => 'required|string',
            'difficulties' => 'nullable|array',
            'difficulties.*' => 'nullable|string',
            'comments' => 'nullable|array',
            'comments.*' => 'nullable|string'
        ]);

        $createdCount = 0;
        $replacedCount = 0;
        $errors = [];

        // Xử lý từng hàng báo cáo
        foreach ($request->report_dates as $index => $reportDate) {
            $reportDateCarbon = Carbon::parse($reportDate);
            
            // Kiểm tra xem đã có báo cáo cho ngày này chưa
            $existingReport = WorkReport::where('user_id', $user->id)
                                       ->where('report_date', $reportDate)
                                       ->first();

            if ($existingReport) {
                // Nếu có báo cáo trùng ngày, hỏi user có muốn thay thế không
                if ($request->has('replace_existing') && $request->replace_existing) {
                    // Xóa báo cáo cũ và tạo mới
                    $existingReport->delete();
                    $replacedCount++;
                } else {
                    $errors[] = "Đã có báo cáo cho ngày " . $reportDateCarbon->format('d/m/Y') . ". Bạn có muốn thay thế không?";
                    continue;
                }
            }

            // Tự động tính toán năm, tháng và tuần từ ngày báo cáo
            $year = $reportDateCarbon->year;
            $month = $reportDateCarbon->month;
            $week = $reportDateCarbon->weekOfYear;

            // Tạo báo cáo mới
            $report = WorkReport::create([
                'user_id' => $user->id,
                'department_id' => $user->department_id ?? null, // Cho phép null cho admin
                'year' => $year,
                'month' => $month,
                'week' => $week,
                'report_date' => $reportDate,
                'daily_work' => $request->daily_works[$index],
                'difficulties' => $request->difficulties[$index] ?? null,
                'comments' => $request->comments[$index] ?? null,
            ]);

            // Gửi thông báo cho Admin và Manager
            NotificationService::workReportSubmitted($report, $user);

            $createdCount++;
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $message = "";
        if ($createdCount > 0) {
            $message .= "Đã tạo thành công {$createdCount} báo cáo. ";
        }
        if ($replacedCount > 0) {
            $message .= "Đã thay thế {$replacedCount} báo cáo cũ. ";
        }
        
        if (empty($message)) {
            $message = "Không có báo cáo nào được tạo.";
        }

        return redirect()->route('work-reports.index')
                        ->with('success', trim($message));
    }

    // Cập nhật báo cáo
    public function update(Request $request, WorkReport $workReport)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền
        if ($workReport->user_id !== $user->id && !$user->isManager() && !$user->isDirector() && !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền cập nhật báo cáo này.');
        }

        $request->validate([
            'daily_work' => 'required|string',
            'difficulties' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);

        $workReport->update([
            'daily_work' => $request->daily_work,
            'difficulties' => $request->difficulties,
            'comments' => $request->comments,
            'custom_fields' => $request->custom_fields
        ]);

        return back()->with('success', 'Báo cáo đã được cập nhật thành công.');
    }

    // Xem báo cáo cụ thể
    public function show(WorkReport $workReport)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền xem báo cáo
        if ($user->isEmployee()) {
            // Employee chỉ xem được báo cáo của mình
            if ($workReport->user_id !== $user->id) {
                abort(403, 'Bạn không có quyền xem báo cáo này.');
            }
        } elseif ($user->isManager()) {
            // Manager xem được báo cáo của nhân viên trong phòng ban
            if ($workReport->user->department_id !== $user->department_id) {
                abort(403, 'Bạn không có quyền xem báo cáo này.');
            }
        }
        // Admin và Director xem được tất cả
        
        // Load relationships
        $workReport->load(['user', 'user.department']);
        
        return view('work-reports.show', compact('workReport'));
    }

    // Xem báo cáo theo tuần
    public function showWeek(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer',
            'week' => 'required|integer',
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id'
        ]);

        $year = $request->year;
        $month = $request->month;
        $week = $request->week;
        $targetUserId = $request->user_id;
        $departmentId = $request->department_id;

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

        // Lấy thông tin tuần
        $weekInfo = WorkReport::getWeekDates($year, $week);

        // Nếu có department_id, lấy báo cáo cho department đó
        if ($departmentId) {
            $reports = WorkReport::with(['user', 'department'])
                                ->where('department_id', $departmentId)
                                ->where('year', $year)
                                ->where('month', $month)
                                ->where('week', $week)
                                ->orderBy('report_date', 'desc')
                                ->get();
        } else {
            $reports = WorkReport::getWeekReports($year, $week, $month, $targetUserId);
        }
        
        return response()->json([
            'reports' => $reports,
            'week_info' => [
                'year' => $year,
                'month' => $month,
                'week' => $week,
                'start_date' => $weekInfo['start_formatted'],
                'end_date' => $weekInfo['end_formatted']
            ]
        ]);
    }

    // API để lấy danh sách tháng cho năm
    public function getMonths(Request $request)
    {
        $request->validate(['year' => 'required|integer']);
        
        $user = Auth::user();
        $userId = $user->isEmployee() ? $user->id : null;
        $departmentId = $request->department_id;
        
        // Nếu có department_id, lấy months cho department đó
        if ($departmentId) {
            $months = WorkReport::where('department_id', $departmentId)
                              ->where('year', $request->year)
                              ->distinct()
                              ->pluck('month')
                              ->sort()
                              ->values();
            return response()->json($months);
        }
        
        $months = WorkReport::getAvailableMonths($request->year, $userId);
        
        return response()->json($months);
    }

    // API để lấy danh sách tuần cho năm và tháng
    public function getWeeks(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'nullable|integer'
        ]);
        
        $user = Auth::user();
        $userId = $user->isEmployee() ? $user->id : null;
        $departmentId = $request->department_id;
        
        // Nếu có department_id, lấy weeks cho department đó
        if ($departmentId) {
            $query = WorkReport::where('department_id', $departmentId)
                              ->where('year', $request->year);
            if ($request->month) {
                $query->where('month', $request->month);
            }
            $weeks = $query->distinct()->pluck('week')->sort()->values();
            return response()->json($weeks);
        }
        
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
                        ->orderBy('name', 'asc')
                        ->get(['id', 'name']);

        return response()->json($employees);
    }

    // API để lấy tất cả báo cáo của một employee
    public function getEmployeeReports(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $targetUser = User::find($request->user_id);
        
        // Manager chỉ có thể xem báo cáo của nhân viên cùng phòng ban
        if ($user->isManager() && $targetUser->department_id !== $user->department_id) {
            abort(403, 'Bạn chỉ có thể xem báo cáo của nhân viên cùng phòng ban.');
        }

        $reports = WorkReport::with(['user', 'department'])
                            ->where('user_id', $request->user_id)
                            ->orderBy('year', 'desc')
                            ->orderBy('month', 'desc')
                            ->orderBy('week', 'desc')
                            ->orderBy('report_date', 'desc')
                            ->get();

        return response()->json([
            'reports' => $reports,
            'total' => $reports->count()
        ]);
    }

    // API để lấy thông tin tuần từ ngày
    public function getWeekFromDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ]);

        $weekInfo = WorkReport::getWeekInfoFromDate($request->date);
        $weekDates = WorkReport::getWeekDates($weekInfo['year'], $weekInfo['week_of_year']);

        return response()->json([
            'year' => $weekInfo['year'],
            'week' => $weekInfo['week_of_year'],
            'week_of_month' => $weekInfo['week_of_month'],
            'month' => $weekInfo['month'],
            'week_info' => $weekDates,
            'comprehensive_info' => $weekInfo
        ]);
    }

    // Xóa báo cáo
    public function destroy(WorkReport $workReport)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền
        if ($workReport->user_id !== $user->id && !$user->isManager() && !$user->isDirector() && !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền xóa báo cáo này.');
        }

        // Manager chỉ có thể xóa báo cáo của nhân viên cùng phòng ban
        if ($user->isManager() && $workReport->department_id !== $user->department_id) {
            abort(403, 'Bạn chỉ có thể xóa báo cáo của nhân viên cùng phòng ban.');
        }

        $workReport->delete();

        return back()->with('success', 'Báo cáo đã được xóa thành công.');
    }

    // Hiển thị báo cáo tuần hiện tại
    public function currentWeek()
    {
        $user = Auth::user();
        $now = Carbon::now();
        
        // Lấy thông tin tuần hiện tại
        $weekInfo = WorkReport::getWeekInfoFromDate($now->format('Y-m-d'));
        $weekDates = WorkReport::getWeekDates($weekInfo['year'], $weekInfo['week_of_year']);
        
        // Lấy báo cáo của tuần hiện tại
        $reports = WorkReport::where('user_id', $user->id)
                            ->where('year', $weekInfo['year'])
                            ->where('week', $weekInfo['week_of_year'])
                            ->orderBy('report_date', 'desc')
                            ->get();
        
        // Thống kê
        $totalReports = $reports->count();
        $completedDays = $reports->count();
        
        return view('work-reports.current-week', compact('reports', 'weekInfo', 'weekDates', 'totalReports', 'completedDays'));
    }

    // Hiển thị báo cáo tháng hiện tại
    public function currentMonth()
    {
        $user = Auth::user();
        $now = Carbon::now();
        
        // Lấy báo cáo của tháng hiện tại
        $reports = WorkReport::where('user_id', $user->id)
                            ->where('year', $now->year)
                            ->where('month', $now->month)
                            ->orderBy('report_date', 'desc')
                            ->get();
        
        // Nhóm theo tuần
        $reportsByWeek = $reports->groupBy('week');
        
        // Thống kê
        $totalReports = $reports->count();
        $totalWeeks = $reportsByWeek->count();
        $completedDays = $reports->count();
        
        // Tính số tuần trong tháng
        $firstDayOfMonth = Carbon::createFromDate($now->year, $now->month, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $weeksInMonth = $firstDayOfMonth->diffInWeeks($lastDayOfMonth) + 1;
        
        // Lấy thông tin tháng
        $monthInfo = [
            'year' => $now->year,
            'month' => $now->month,
            'month_name' => $now->format('F Y'),
            'days_in_month' => $now->daysInMonth,
            'weeks_in_month' => $weeksInMonth
        ];
        
        return view('work-reports.current-month', compact('reports', 'reportsByWeek', 'monthInfo', 'totalReports', 'completedDays', 'totalWeeks'));
    }

    // Hiển thị hoạt động cá nhân
    public function myActivity(Request $request)
    {
        $user = Auth::user();
        $selectedDays = $request->get('days', 30); // Mặc định 30 ngày
        
        // Lấy báo cáo gần đây theo số ngày được chọn
        $recentReports = WorkReport::where('user_id', $user->id)
                                  ->where('report_date', '>=', Carbon::now()->subDays($selectedDays))
                                  ->orderBy('report_date', 'desc')
                                  ->get();
        
        // Thống kê theo tuần
        $weeklyStats = WorkReport::where('user_id', $user->id)
                                ->where('report_date', '>=', Carbon::now()->subDays($selectedDays))
                                ->selectRaw('year, week, COUNT(*) as report_count')
                                ->groupBy('year', 'week')
                                ->orderBy('year', 'desc')
                                ->orderBy('week', 'desc')
                                ->get();
        
        // Thống kê theo tháng (luôn lấy 90 ngày để có đủ dữ liệu)
        $monthlyStats = WorkReport::where('user_id', $user->id)
                                 ->where('report_date', '>=', Carbon::now()->subDays(90))
                                 ->selectRaw('year, month, COUNT(*) as report_count')
                                 ->groupBy('year', 'month')
                                 ->orderBy('year', 'desc')
                                 ->orderBy('month', 'desc')
                                 ->get();
        
        // Tổng quan
        $totalReports = WorkReport::where('user_id', $user->id)->count();
        $thisMonthReports = WorkReport::where('user_id', $user->id)
                                    ->where('year', Carbon::now()->year)
                                    ->where('month', Carbon::now()->month)
                                    ->count();
        $thisWeekReports = WorkReport::where('user_id', $user->id)
                                   ->where('year', Carbon::now()->year)
                                   ->where('week', Carbon::now()->weekOfYear)
                                   ->count();
        
        return view('work-reports.my-activity', compact(
            'recentReports', 
            'weeklyStats', 
            'monthlyStats', 
            'totalReports', 
            'thisMonthReports', 
            'thisWeekReports',
            'selectedDays'
        ));
    }

    // Đánh dấu báo cáo đã đọc
    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'report_id' => 'required|exists:work_reports,id',
                'is_read' => 'required|boolean'
            ]);

            $report = WorkReport::findOrFail($request->report_id);

            // Chỉ admin và manager mới có thể đánh dấu báo cáo đã đọc
            if (!Auth::user()->isAdmin() && !Auth::user()->isDirector() && !Auth::user()->isManager()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này'
                ], 403);
            }

            // Nếu là manager, kiểm tra xem báo cáo có thuộc phòng ban của manager không
            if (Auth::user()->isManager()) {
                if ($report->user->department_id !== Auth::user()->department_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn chỉ có thể quản lý báo cáo của nhân viên trong phòng ban của mình'
                    ], 403);
                }
            }

            // Kiểm tra quyền thay đổi
            $currentUser = Auth::user();
            
            // Nếu admin đã check và người hiện tại là manager, không cho phép thay đổi
            if ($currentUser->isManager() && $report->read_by) {
                $checker = User::find($report->read_by);
                if ($checker && ($checker->isAdmin() || $checker->isDirector())) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Manager không thể thay đổi trạng thái đã được admin check'
                    ], 403);
                }
            }
            
            // Cập nhật trạng thái đã đọc
            $report->update([
                'is_read' => $request->is_read,
                'read_at' => $request->is_read ? now() : null,
                'read_by' => $request->is_read ? Auth::id() : null,
                'rejected_at' => null, // Reset rejected status if marked as read
                'rejected_by' => null,
                'rejection_reason' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->is_read ? 'Đã đánh dấu báo cáo đã đọc' : 'Đã bỏ đánh dấu báo cáo đã đọc'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Từ chối/Hoàn tác từ chối báo cáo
    public function reject(Request $request)
    {
        try {
            $request->validate([
                'report_id' => 'required|exists:work_reports,id'
            ]);

            $report = WorkReport::findOrFail($request->report_id);

            // Chỉ admin và manager mới có thể từ chối báo cáo
            if (!Auth::user()->isAdmin() && !Auth::user()->isDirector() && !Auth::user()->isManager()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này'
                ], 403);
            }

            // Nếu là manager, kiểm tra xem báo cáo có thuộc phòng ban của manager không
            if (Auth::user()->isManager()) {
                if ($report->user->department_id !== Auth::user()->department_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn chỉ có thể quản lý báo cáo của nhân viên trong phòng ban của mình'
                    ], 403);
                }
            }

            // Kiểm tra quyền thay đổi
            $currentUser = Auth::user();
            
            // Nếu admin đã reject và người hiện tại là manager, không cho phép thay đổi
            if ($currentUser->isManager() && $report->rejected_by) {
                $rejecter = User::find($report->rejected_by);
                if ($rejecter && ($rejecter->isAdmin() || $rejecter->isDirector())) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Manager không thể thay đổi trạng thái đã được admin reject'
                    ], 403);
                }
            }
            
            // Nếu đã bị từ chối thì hoàn tác, nếu chưa thì từ chối
            if ($report->rejected_at) {
                // Hoàn tác từ chối
                $report->update([
                    'rejected_at' => null,
                    'rejected_by' => null,
                    'rejection_reason' => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đã hoàn tác từ chối báo cáo',
                    'action' => 'undo'
                ]);
            } else {
                // Từ chối báo cáo
                $report->update([
                    'is_read' => false,
                    'read_at' => null,
                    'read_by' => null,
                    'rejected_at' => now(),
                    'rejected_by' => Auth::id(),
                    'rejection_reason' => 'Báo cáo bị từ chối bởi ' . ($currentUser->isAdmin() ? 'admin' : ($currentUser->isDirector() ? 'director' : 'manager'))
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đã từ chối báo cáo thành công',
                    'action' => 'reject'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
