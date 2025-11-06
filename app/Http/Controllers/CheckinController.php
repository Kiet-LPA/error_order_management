<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checkin;
use App\Models\CheckinRegion;
use App\Models\GpsRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckinController extends Controller
{
    /**
     * Calculate distance between two GPS points (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);
        
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;
        
        $a = sin($deltaLat/2) * sin($deltaLat/2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon/2) * sin($deltaLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * Get current session (morning/evening) - DEPRECATED
     * Now using simple checkin/checkout system
     */
    private function getCurrentSession()
    {
        // Keep for backward compatibility but not used in new system
        return null;
    }

    /**
     * Check if user can check-in now - DEPRECATED
     * Now using simple checkin/checkout system
     */
    private function canCheckIn()
    {
        // Always allow checkin/checkout
        return true;
    }

    /**
     * Generate GPS code for failed check-ins
     */
    private function generateGPSCode($userId, $regionId)
    {
        return strtoupper(substr(md5($userId . '_' . $regionId . '_' . date('Y-m-d')), 0, 8));
    }

    /**
     * Kiểm tra xem user có phòng ban nào có GPS không
     */
    private function userHasAnyDepartmentWithGps($user)
    {
        // Lấy tất cả phòng ban mà user đã được assign
        $assignedDepartments = collect();
        
        // Thêm department chính nếu có
        if ($user->department) {
            $assignedDepartments->push($user->department);
        }
        
        // Thêm các departments từ bảng user_departments
        $additionalDepartments = $user->departments()->get();
        $assignedDepartments = $assignedDepartments->merge($additionalDepartments);
        
        // Kiểm tra xem có phòng ban nào có GPS không
        return $assignedDepartments
            ->unique('id')
            ->filter(function ($department) {
                return $department->hasGpsConfig();
            })
            ->isNotEmpty();
    }

    /**
     * Lấy phòng ban đầu tiên có GPS của user
     */
    private function getFirstDepartmentWithGps($user)
    {
        // Lấy tất cả phòng ban mà user đã được assign
        $assignedDepartments = collect();
        
        // Thêm department chính nếu có
        if ($user->department) {
            $assignedDepartments->push($user->department);
        }
        
        // Thêm các departments từ bảng user_departments
        $additionalDepartments = $user->departments()->get();
        $assignedDepartments = $assignedDepartments->merge($additionalDepartments);
        
        // Tìm phòng ban đầu tiên có GPS
        return $assignedDepartments
            ->unique('id')
            ->filter(function ($department) {
                return $department->hasGpsConfig();
            })
            ->first();
    }

    /**
     * Display checkin page for employee
     */
    public function index()
    {
        $user = Auth::user();
        
        // Kiểm tra xem user có phòng ban nào có GPS không
        $hasAnyDepartmentWithGps = $this->userHasAnyDepartmentWithGps($user);
        
        if (!$hasAnyDepartmentWithGps) {
            return view('checkin.no-region');
        }
        
        // Lấy phòng ban chính để hiển thị thông tin ban đầu (nếu có GPS)
        // Nếu phòng ban chính không có GPS, lấy phòng ban đầu tiên có GPS
        $department = $user->getCheckinDepartment();
        if (!$department || !$department->hasGpsConfig()) {
            $department = $this->getFirstDepartmentWithGps($user);
        }

        $today = Carbon::today();
        
        // Get today's checkins (both morning and evening)
        $todayCheckins = $user->checkins()
            ->where('checkin_date', $today)
            ->orderBy('checkin_time')
            ->get();

        // Check if already checked in today
        $hasCheckin = $todayCheckins->where('session', 'checkin')->first();
        $hasCheckout = $todayCheckins->where('session', 'checkout')->first();
        
        // Calculate total working hours if both checkin and checkout exist
        $totalWorkingHours = 0;
        if ($hasCheckin && $hasCheckout) {
            $checkinTime = Carbon::parse($hasCheckin->checkin_time);
            $checkoutTime = Carbon::parse($hasCheckout->checkin_time);
            $totalWorkingHours = $checkoutTime->diffInHours($checkinTime);
        }
        
        // Get GPS requests for today
        $gpsRequest = $user->gpsRequests()
            ->where('request_date', $today)
            ->first();

        return view('checkin.index', compact(
            'user', 
            'department', 
            'todayCheckins', 
            'hasCheckin', 
            'hasCheckout', 
            'totalWorkingHours',
            'gpsRequest'
        ));
    }

    /**
     * Tìm phòng ban hợp lệ (trong bán kính) từ danh sách phòng ban của user
     */
    private function findValidDepartment($user, $latitude, $longitude)
    {
        // Lấy tất cả phòng ban mà user đã được assign
        $assignedDepartments = collect();
        
        // Thêm department chính nếu có
        if ($user->department) {
            $assignedDepartments->push($user->department);
        }
        
        // Thêm các departments từ bảng user_departments
        $additionalDepartments = $user->departments()->get();
        $assignedDepartments = $assignedDepartments->merge($additionalDepartments);
        
        // Loại bỏ duplicate và chỉ lấy những department có GPS
        $departmentsWithGps = $assignedDepartments
            ->unique('id')
            ->filter(function ($department) {
                return $department->hasGpsConfig();
            });
        
        if ($departmentsWithGps->isEmpty()) {
            return null;
        }
        
        // Tìm phòng ban gần nhất và trong bán kính
        $validDepartment = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($departmentsWithGps as $department) {
            $distance = $this->calculateDistance(
                $latitude, 
                $longitude, 
                $department->latitude, 
                $department->longitude
            );
            
            // Nếu trong bán kính và gần nhất
            if ($distance <= $department->radius_meters && $distance < $minDistance) {
                $minDistance = $distance;
                $validDepartment = $department;
            }
        }
        
        return $validDepartment;
    }

    /**
     * Process điểm danh/kết thúc ca request
     */
    public function checkin(Request $request)
    {
        try {
            \Log::info('Điểm danh request received', [
                'user_id' => Auth::id(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'action' => $request->action
            ]);

            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'action' => 'required|in:checkin,checkout',
                'accuracy' => 'nullable|numeric|min:0|max:1000', // GPS accuracy in meters
            ]);

            $user = Auth::user();
            
            // ✅ KIỂM TRA ĐỘ CHÍNH XÁC GPS (nếu có)
            if ($request->has('accuracy') && $request->accuracy > 100) {
                \Log::warning('GPS accuracy too low', [
                    'user_id' => $user->id,
                    'accuracy' => $request->accuracy,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "GPS không đủ chính xác. Độ chính xác hiện tại: " . round($request->accuracy) . "m (yêu cầu < 100m). Vui lòng di chuyển đến nơi có tín hiệu GPS tốt hơn."
                ], 400);
            }
        
        $today = Carbon::today();
        $action = $request->action; // 'checkin' or 'checkout'

        // Kiểm tra đã thực hiện hành động này hôm nay chưa
        $existingCheckin = $user->checkins()
            ->where('checkin_date', $today)
            ->where('session', $action)
            ->first();

        if ($existingCheckin) {
            $actionText = $action === 'checkin' ? 'điểm danh' : 'kết thúc ca';
            return response()->json([
                'success' => false,
                'message' => "Bạn đã {$actionText} hôm nay rồi!"
            ], 400);
        }

        // Kiểm tra nếu là kết thúc ca thì phải đã điểm danh
        if ($action === 'checkout') {
            $hasCheckin = $user->checkins()
                ->where('checkin_date', $today)
                ->where('session', 'checkin')
                ->first();
            
            if (!$hasCheckin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa điểm danh hôm nay. Vui lòng điểm danh trước khi kết thúc ca.'
                ], 400);
            }
        }
        
        // Tìm phòng ban hợp lệ (trong bán kính) từ các phòng ban của user
        $validDepartment = $this->findValidDepartment($user, $request->latitude, $request->longitude);
        
        if ($validDepartment) {
            // Tính khoảng cách
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $validDepartment->latitude,
                $validDepartment->longitude
            );

            // Thành công - điểm danh/kết thúc ca
            $checkin = Checkin::create([
                'user_id' => $user->id,
                'department_id' => $validDepartment->id,
                'checkin_date' => $today,
                'session' => $action,
                'checkin_time' => now(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'distance_meters' => $distance,
                'ip_address' => $request->ip(),
                'status' => 'success',
            ]);

            $actionText = $action === 'checkin' ? 'Điểm danh' : 'Kết thúc ca';
            return response()->json([
                'success' => true,
                'message' => "{$actionText} thành công tại phòng ban: {$validDepartment->name}!",
                'checkin' => $checkin,
                'department' => [
                    'id' => $validDepartment->id,
                    'name' => $validDepartment->name,
                    'address' => $validDepartment->address,
                    'radius_meters' => $validDepartment->radius_meters,
                    'distance' => round($distance)
                ]
            ]);
        } else {
            // Không có phòng ban hợp lệ trong bán kính
            // Nếu là kết thúc ca, tìm phòng ban đã điểm danh để gửi GPS request
            $targetDepartment = null;
            
            if ($action === 'checkout') {
                // Tìm phòng ban đã điểm danh hôm nay
                $todayCheckin = $user->checkins()
                    ->where('checkin_date', $today)
                    ->where('session', 'checkin')
                    ->first();
                
                if ($todayCheckin && $todayCheckin->department) {
                    $targetDepartment = $todayCheckin->department;
                }
            }
            
            // Nếu không tìm thấy phòng ban đã điểm danh, tìm phòng ban gần nhất
            if (!$targetDepartment) {
                $targetDepartment = $user->getNearestDepartmentWithGps($request->latitude, $request->longitude);
            }
            
            if (!$targetDepartment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy phòng ban nào có cấu hình GPS trong số các phòng ban bạn được phân công.'
                ], 400);
            }
            
            // Tính khoảng cách đến phòng ban target
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $targetDepartment->latitude,
                $targetDepartment->longitude
            );
            
            // Tạo GPS request
            $gpsCode = $this->generateGPSCode($user->id, $targetDepartment->id);
            
            GpsRequest::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'request_date' => $today,
                    'session' => $action,
                ],
                [
                    'department_id' => $targetDepartment->id,
                    'session' => $action,
                    'distance_meters' => $distance,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'gps_code' => $gpsCode,
                    'status' => 'pending',
                ]
            );

            $actionText = $action === 'checkin' ? 'điểm danh' : 'kết thúc ca';
            return response()->json([
                'success' => false,
                'message' => "Bạn đang ở ngoài khu vực điểm danh của phòng ban {$targetDepartment->name}. Khoảng cách: " . round($distance) . "m (cho phép: " . $targetDepartment->radius_meters . "m). Mã GPS: " . $gpsCode,
                'gps_code' => $gpsCode,
                'distance' => round($distance),
                'allowed_distance' => $targetDepartment->radius_meters,
                'department' => [
                    'id' => $targetDepartment->id,
                    'name' => $targetDepartment->name,
                    'address' => $targetDepartment->address,
                    'radius_meters' => $targetDepartment->radius_meters,
                    'distance' => round($distance)
                ],
                'coordinates' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'google_maps_url' => "https://www.google.com/maps?q={$request->latitude},{$request->longitude}"
                ]
            ]);
        }
        } catch (\Exception $e) {
            \Log::error('Điểm danh error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý điểm danh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display checkin history
     */
    public function history()
    {
        $user = Auth::user();
        
        $checkins = $user->checkins()
            ->with('department')
            ->orderBy('checkin_date', 'desc')
            ->orderBy('checkin_time', 'desc')
            ->paginate(20);

        return view('checkin.history', compact('checkins'));
    }
}
