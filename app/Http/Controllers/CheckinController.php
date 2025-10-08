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
     * Display checkin page for employee
     */
    public function index()
    {
        $user = Auth::user();
        $department = $user->getCheckinDepartment();
        
        if (!$department || !$department->hasGpsConfig()) {
            return view('checkin.no-region');
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
     * Process checkin request (simplified - just checkin/checkout)
     */
    public function checkin(Request $request)
    {
        try {
            \Log::info('Checkin request received', [
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
            $department = $user->getCheckinDepartment();
            
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
            
            \Log::info('User department info', [
                'user_id' => $user->id,
                'department_id' => $department ? $department->id : null,
                'department_name' => $department ? $department->name : null,
                'has_gps_config' => $department ? $department->hasGpsConfig() : false,
                'gps_accuracy' => $request->accuracy ?? 'not_provided'
            ]);
        
        if (!$department || !$department->hasGpsConfig()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa được phân công khu vực điểm danh.'
            ], 400);
        }

        $today = Carbon::today();
        $action = $request->action; // 'checkin' or 'checkout'

        // Check if already performed this action today
        $existingCheckin = $user->checkins()
            ->where('checkin_date', $today)
            ->where('session', $action)
            ->first();

        if ($existingCheckin) {
            $actionText = $action === 'checkin' ? 'checkin' : 'checkout';
            return response()->json([
                'success' => false,
                'message' => "Bạn đã {$actionText} hôm nay rồi!"
            ], 400);
        }

        // Calculate distance
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $department->latitude,
            $department->longitude
        );

        // Check if within radius
        if ($distance <= $department->radius_meters) {
            // Successful checkin/checkout
            $checkin = Checkin::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'checkin_date' => $today,
                'session' => $action, // 'checkin' or 'checkout'
                'checkin_time' => now(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'distance_meters' => $distance,
                'ip_address' => $request->ip(),
                'status' => 'success',
            ]);

            $actionText = $action === 'checkin' ? 'Checkin' : 'Checkout';
            return response()->json([
                'success' => true,
                'message' => "{$actionText} thành công!",
                'checkin' => $checkin
            ]);
        } else {
            // Failed checkin/checkout - create GPS request
            $gpsCode = $this->generateGPSCode($user->id, $department->id);
            
            GpsRequest::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'request_date' => $today,
                    'session' => $action, // 'checkin' or 'checkout'
                ],
                [
                    'department_id' => $department->id,
                    'session' => $action, // 'checkin' or 'checkout'
                    'distance_meters' => $distance,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'gps_code' => $gpsCode,
                    'status' => 'pending',
                ]
            );

            $actionText = $action === 'checkin' ? 'checkin' : 'checkout';
            return response()->json([
                'success' => false,
                'message' => "Bạn đang ở ngoài khu vực điểm danh. Khoảng cách: " . round($distance) . "m (cho phép: " . $department->radius_meters . "m). Mã GPS: " . $gpsCode,
                'gps_code' => $gpsCode,
                'distance' => round($distance),
                'allowed_distance' => $department->radius_meters,
                'coordinates' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'google_maps_url' => "https://www.google.com/maps?q={$request->latitude},{$request->longitude}"
                ]
            ]);
        }
        } catch (\Exception $e) {
            \Log::error('Checkin error', [
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
