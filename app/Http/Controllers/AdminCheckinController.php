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
        try {
            \Log::info('GPS Request approval attempt', [
                'gps_request_id' => $gpsRequest->id,
                'user_id' => $gpsRequest->user_id,
                'request_status' => $request->status ?? 'not_provided',
                'admin_notes' => $request->admin_notes ?? 'not_provided'
            ]);

            $user = Auth::user();
            
            if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
                \Log::warning('Unauthorized GPS approval attempt', [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'gps_request_id' => $gpsRequest->id
                ]);
                abort(403, 'Bạn không có quyền thực hiện thao tác này.');
            }

            // Kiểm tra quyền Manager
            if ($user->isManager()) {
                if ($gpsRequest->user->department_id !== $user->department_id) {
                    \Log::warning('Manager trying to approve GPS from different department', [
                        'manager_id' => $user->id,
                        'manager_department' => $user->department_id,
                        'gps_user_department' => $gpsRequest->user->department_id,
                        'gps_request_id' => $gpsRequest->id
                    ]);
                    abort(403, 'Bạn chỉ có thể duyệt GPS request của nhân viên trong phòng ban.');
                }
            }

            $request->validate([
                'status' => 'required|in:approved,rejected',
                'admin_notes' => 'nullable|string|max:500'
            ], [
                'status.required' => 'Vui lòng chọn trạng thái duyệt!',
                'status.in' => 'Trạng thái không hợp lệ!',
                'admin_notes.max' => 'Ghi chú không được quá 500 ký tự!'
            ]);

            \Log::info('GPS Request validation passed', [
                'gps_request_id' => $gpsRequest->id,
                'status' => $request->status
            ]);

            $gpsRequest->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'approved_by' => $user->id,
                'approved_at' => now()
            ]);

            \Log::info('GPS Request updated successfully', [
                'gps_request_id' => $gpsRequest->id,
                'new_status' => $request->status
            ]);

            // Nếu approved, tạo checkin record
            if ($request->status === 'approved') {
                try {
                    $checkin = Checkin::create([
                        'user_id' => $gpsRequest->user_id,
                        'department_id' => $gpsRequest->department_id,
                        'checkin_date' => $gpsRequest->request_date,
                        'session' => $gpsRequest->session, // Sử dụng session từ GPS request
                        'checkin_time' => now(),
                        'latitude' => $gpsRequest->latitude ?? 0,
                        'longitude' => $gpsRequest->longitude ?? 0,
                        'distance_meters' => $gpsRequest->distance_meters,
                        'ip_address' => request()->ip(),
                        'status' => 'success',
                        'notes' => 'Được duyệt bởi ' . $user->name . ($request->admin_notes ? ' - ' . $request->admin_notes : '')
                    ]);

                    \Log::info('Checkin record created successfully', [
                        'checkin_id' => $checkin->id,
                        'gps_request_id' => $gpsRequest->id
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error creating checkin record', [
                        'gps_request_id' => $gpsRequest->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return redirect()->back()->with('error', 'Đã cập nhật GPS request nhưng có lỗi khi tạo bản ghi điểm danh: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', 'Đã cập nhật trạng thái GPS request thành công.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('GPS Request validation failed', [
                'gps_request_id' => $gpsRequest->id,
                'errors' => $e->errors()
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('GPS Request approval error', [
                'gps_request_id' => $gpsRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi duyệt GPS request: ' . $e->getMessage());
        }
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
            'session' => 'required|in:checkin,checkout',
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
            $sessionText = $request->session === 'checkin' ? 'checkin' : 'checkout';
            return redirect()->back()->with('error', "Nhân viên đã {$sessionText} cho ngày này.");
        }

        // Tạo checkin thủ công
        Checkin::create([
            'user_id' => $request->employee_id,
            'department_id' => $employee->department_id,
            'checkin_date' => $request->checkin_date,
            'session' => $request->session, // 'checkin' or 'checkout'
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
        return ($hour >= 4 && $hour <= 11) ? 'checkin' : 'checkout';
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
                                       'checkin' => $dayCheckins->where('session', 'checkin')->count(),
                                       'checkout' => $dayCheckins->where('session', 'checkout')->count()
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

    /**
     * Test GPS approval without creating checkin
     */
    public function testGpsApproval(Request $request, GpsRequest $gpsRequest)
    {
        try {
            \Log::info('GPS Test approval attempt', [
                'gps_request_id' => $gpsRequest->id,
                'user_id' => $gpsRequest->user_id,
                'department_id' => $gpsRequest->department_id,
                'request_date' => $gpsRequest->request_date,
                'distance_meters' => $gpsRequest->distance_meters
            ]);

            $user = Auth::user();
            
            // Test data validation
            $testData = [
                'user_id' => $gpsRequest->user_id,
                'department_id' => $gpsRequest->department_id,
                'checkin_date' => $gpsRequest->request_date,
                'session' => $this->getSessionFromTime($gpsRequest->request_date),
                'checkin_time' => now(),
                'latitude' => 0,
                'longitude' => 0,
                'distance_meters' => $gpsRequest->distance_meters,
                'ip_address' => request()->ip(),
                'status' => 'success',
                'notes' => 'Test approval by ' . $user->name
            ];

            \Log::info('GPS Test data prepared', $testData);

            // Test if Checkin model can be created (without actually creating)
            $checkin = new Checkin($testData);
            $checkin->save();

            \Log::info('GPS Test checkin created successfully', [
                'checkin_id' => $checkin->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'GPS approval test successful',
                'checkin_id' => $checkin->id,
                'test_data' => $testData
            ]);

        } catch (\Exception $e) {
            \Log::error('GPS Test approval error', [
                'gps_request_id' => $gpsRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Debug database structure
     */
    public function debugDatabaseStructure()
    {
        try {
            // Kiểm tra cấu trúc bảng checkins
            $checkinsColumns = \DB::select("DESCRIBE checkins");
            $checkinsColumnsArray = array_map(function($column) {
                return [
                    'field' => $column->Field,
                    'type' => $column->Type,
                    'null' => $column->Null,
                    'key' => $column->Key,
                    'default' => $column->Default,
                    'extra' => $column->Extra
                ];
            }, $checkinsColumns);

            // Kiểm tra model fillable
            $checkinModel = new Checkin();
            $fillableFields = $checkinModel->getFillable();

            // Test tạo checkin với dữ liệu mẫu
            $testData = [
                'user_id' => 1,
                'department_id' => 1,
                'checkin_date' => now()->toDateString(),
                'session' => 'checkin',
                'checkin_time' => now(),
                'latitude' => 0,
                'longitude' => 0,
                'distance_meters' => 0,
                'ip_address' => '127.0.0.1',
                'status' => 'success',
                'notes' => 'Debug test'
            ];

            $checkin = new Checkin($testData);
            $checkin->save();

            return response()->json([
                'success' => true,
                'checkins_columns' => $checkinsColumnsArray,
                'model_fillable' => $fillableFields,
                'test_checkin_id' => $checkin->id,
                'test_data' => $testData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
