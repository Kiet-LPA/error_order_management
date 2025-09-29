<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checkin;
use App\Models\GpsRequest;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminCheckinController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,director,manager');
    }

    /**
     * Admin/Director: Dashboard quản lý điểm danh toàn công ty
     */
    public function index()
    {
        $user = Auth::user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Base query dựa trên quyền
        $baseQuery = Checkin::with(['user', 'department']);
        
        if ($user->isManager()) {
            // Manager chỉ thấy checkins của phòng ban mình
            $baseQuery->whereHas('user', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }
        // Admin và Director thấy tất cả

        // Thống kê
        $stats = $this->getStats($user);
        
        // Điểm danh gần đây (7 ngày)
        $recentCheckins = $baseQuery->clone()
            ->where('checkin_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('checkin_time', 'desc')
            ->limit(20)
            ->get();

        // GPS Requests chờ duyệt
        $pendingGpsRequests = $this->getPendingGpsRequests($user);

        return view('admin.checkin.index', compact('stats', 'recentCheckins', 'pendingGpsRequests', 'user'));
    }

    /**
     * Quản lý tất cả checkins với filter
     */
    public function manage(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = Checkin::with(['user', 'department']);

        // Filter theo quyền
        if ($user->isManager()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        // Filter theo ngày
        if ($request->filled('date_from')) {
            $query->where('checkin_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('checkin_date', '<=', $request->date_to);
        }

        // Filter theo phòng ban
        if ($request->filled('department_id') && ($user->isAdmin() || $user->isDirector())) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter theo session
        if ($request->filled('session')) {
            $query->where('session', $request->session);
        }

        // Filter theo status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $checkins = $query->orderBy('checkin_date', 'desc')
                         ->orderBy('checkin_time', 'desc')
                         ->paginate(20);

        $departments = Department::orderBy('name')->get();

        return view('admin.checkin.manage', compact('checkins', 'departments', 'user'));
    }

    /**
     * Duyệt GPS Requests
     */
    public function gpsRequests(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = GpsRequest::with(['user', 'department']);

        // Filter theo quyền
        if ($user->isManager()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        // Filter theo status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $gpsRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.checkin.gps-requests', compact('gpsRequests', 'user'));
    }

    /**
     * Duyệt GPS Request
     */
    public function approveGpsRequest(Request $request, GpsRequest $gpsRequest)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Kiểm tra quyền Manager
        if ($user->isManager()) {
            if ($gpsRequest->user->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể duyệt GPS request của nhân viên trong phòng ban.');
            }
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $gpsRequest->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'approved_by' => $user->id,
            'approved_at' => now()
        ]);

        // Nếu approved, tạo checkin record
        if ($request->status === 'approved') {
            Checkin::create([
                'user_id' => $gpsRequest->user_id,
                'department_id' => $gpsRequest->department_id,
                'checkin_date' => $gpsRequest->request_date,
                'session' => $this->getSessionFromTime($gpsRequest->request_date),
                'checkin_time' => now(),
                'latitude' => 0, // Manual approval
                'longitude' => 0,
                'distance_meters' => $gpsRequest->distance_meters,
                'ip_address' => request()->ip(),
                'status' => 'success',
                'notes' => 'Được duyệt bởi ' . $user->name . ($request->admin_notes ? ' - ' . $request->admin_notes : '')
            ]);
        }

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái GPS request thành công.');
    }

    /**
     * Sửa điểm danh thủ công
     */
    public function fixAttendance(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'checkin_date' => 'required|date',
            'session' => 'required|in:morning,evening',
            'notes' => 'required|string|max:500'
        ]);

        $employee = User::findOrFail($request->employee_id);

        // Kiểm tra quyền Manager
        if ($user->isManager()) {
            if ($employee->department_id !== $user->department_id) {
                return redirect()->back()->with('error', 'Bạn chỉ có thể sửa điểm danh cho nhân viên trong phòng ban.');
            }
        }

        // Kiểm tra đã có checkin chưa
        $existingCheckin = Checkin::where('user_id', $request->employee_id)
                                 ->where('checkin_date', $request->checkin_date)
                                 ->where('session', $request->session)
                                 ->first();

        if ($existingCheckin) {
            return redirect()->back()->with('error', 'Nhân viên đã điểm danh cho ca này.');
        }

        // Tạo checkin thủ công
        Checkin::create([
            'user_id' => $request->employee_id,
            'department_id' => $employee->department_id,
            'checkin_date' => $request->checkin_date,
            'session' => $request->session,
            'checkin_time' => now(),
            'latitude' => $employee->department->latitude ?? 0,
            'longitude' => $employee->department->longitude ?? 0,
            'distance_meters' => 0,
            'ip_address' => request()->ip(),
            'status' => 'success',
            'notes' => 'Sửa lỗi bởi ' . $user->name . ' - ' . $request->notes
        ]);

        return redirect()->back()->with('success', 'Đã thêm điểm danh thủ công thành công.');
    }

    /**
     * Xóa điểm danh
     */
    public function deleteCheckin(Checkin $checkin)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Kiểm tra quyền Manager
        if ($user->isManager()) {
            if ($checkin->user->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xóa điểm danh của nhân viên trong phòng ban.');
            }
        }

        $checkin->delete();

        return redirect()->back()->with('success', 'Đã xóa điểm danh thành công.');
    }

    /**
     * Báo cáo điểm danh
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $departmentId = $request->get('department_id');

        $query = Checkin::with(['user', 'department'])
                       ->whereBetween('checkin_date', [$dateFrom, $dateTo]);

        // Filter theo quyền
        if ($user->isManager()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $reports = $this->generateReports($query, $dateFrom, $dateTo);
        $departments = Department::orderBy('name')->get();

        return view('admin.checkin.reports', compact('reports', 'departments', 'dateFrom', 'dateTo', 'departmentId', 'user'));
    }

    /**
     * Lấy thống kê
     */
    private function getStats($user)
    {
        $baseQuery = Checkin::query();
        
        if ($user->isManager()) {
            $baseQuery->whereHas('user', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        $today = Carbon::today();

        return [
            'total_checkins' => $baseQuery->count(),
            'today_checkins' => $baseQuery->clone()->where('checkin_date', $today)->count(),
            'today_success' => $baseQuery->clone()->where('checkin_date', $today)->where('status', 'success')->count(),
            'total_users' => $user->isManager() 
                ? User::where('department_id', $user->department_id)->count()
                : User::count(),
            'success_rate' => $this->calculateSuccessRate($baseQuery, $today)
        ];
    }

    /**
     * Lấy GPS requests chờ duyệt
     */
    private function getPendingGpsRequests($user)
    {
        $query = GpsRequest::with(['user', 'department'])
                          ->where('status', 'pending');

        if ($user->isManager()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        return $query->orderBy('created_at', 'desc')->limit(10)->get();
    }

    /**
     * Tính tỷ lệ thành công
     */
    private function calculateSuccessRate($baseQuery, $date)
    {
        $total = $baseQuery->clone()->where('checkin_date', $date)->count();
        if ($total === 0) return 0;
        
        $success = $baseQuery->clone()->where('checkin_date', $date)->where('status', 'success')->count();
        return round(($success / $total) * 100, 1);
    }

    /**
     * Lấy session từ thời gian
     */
    private function getSessionFromTime($date)
    {
        $hour = Carbon::parse($date)->hour;
        return ($hour >= 4 && $hour <= 11) ? 'morning' : 'evening';
    }

    /**
     * Tạo báo cáo
     */
    private function generateReports($query, $dateFrom, $dateTo)
    {
        $checkins = $query->get();
        
        // Báo cáo theo ngày
        $dailyReport = $checkins->groupBy('checkin_date')
                               ->map(function($dayCheckins) {
                                   return [
                                       'total' => $dayCheckins->count(),
                                       'success' => $dayCheckins->where('status', 'success')->count(),
                                       'morning' => $dayCheckins->where('session', 'morning')->count(),
                                       'evening' => $dayCheckins->where('session', 'evening')->count()
                                   ];
                               });

        // Báo cáo theo phòng ban
        $departmentReport = $checkins->groupBy('department.name')
                                   ->map(function($deptCheckins) {
                                       return [
                                           'total' => $deptCheckins->count(),
                                           'success' => $deptCheckins->where('status', 'success')->count(),
                                           'users' => $deptCheckins->pluck('user.name')->unique()->count()
                                       ];
                                   });

        return [
            'daily' => $dailyReport,
            'department' => $departmentReport,
            'summary' => [
                'total_checkins' => $checkins->count(),
                'success_rate' => $checkins->count() > 0 ? round(($checkins->where('status', 'success')->count() / $checkins->count()) * 100, 1) : 0,
                'total_users' => $checkins->pluck('user.name')->unique()->count()
            ]
        ];
    }
}
